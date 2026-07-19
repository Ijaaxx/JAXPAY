<?php
session_start();
header('Content-Type: application/json');
require_once '../koneksi.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Sesi habis']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid method']); exit; }

$from_id   = (int)$_SESSION['user_id'];
$to_id     = (int)($_POST['to_user_id'] ?? 0);
$jumlah    = (float)($_POST['jumlah'] ?? 0);
$catatan   = sanitize($_POST['catatan'] ?? '');

// Validasi dasar
if ($to_id <= 0 || $jumlah < 1000) {
    echo json_encode(['success'=>false,'message'=>'Data transfer tidak valid. Minimal Rp 1.000']);
    exit;
}
if ($to_id === $from_id) {
    echo json_encode(['success'=>false,'message'=>'Tidak bisa transfer ke diri sendiri']);
    exit;
}

// Mulai transaksi DB
$koneksi->begin_transaction();

try {
    // Lock dan cek saldo pengirim
    $sender = $koneksi->query("SELECT id,nama,saldo FROM users WHERE id=$from_id FOR UPDATE")->fetch_assoc();
    if (!$sender) throw new Exception('Pengirim tidak ditemukan');
    if ($sender['saldo'] < $jumlah) throw new Exception('Saldo tidak mencukupi');

    // Cek penerima
    $receiver = $koneksi->query("SELECT id,nama,saldo FROM users WHERE id=$to_id AND is_active=1 FOR UPDATE")->fetch_assoc();
    if (!$receiver) throw new Exception('Penerima tidak ditemukan atau tidak aktif');

    $saldo_from_before = $sender['saldo'];
    $saldo_from_after  = $saldo_from_before - $jumlah;
    $saldo_to_before   = $receiver['saldo'];
    $saldo_to_after    = $saldo_to_before + $jumlah;

    // Update saldo pengirim
    $koneksi->query("UPDATE users SET saldo=$saldo_from_after WHERE id=$from_id");
    // Update saldo penerima
    $koneksi->query("UPDATE users SET saldo=$saldo_to_after WHERE id=$to_id");

    $kode_keluar = generateKode('TRX');
    $kode_masuk  = generateKode('TRX');
    $ket_keluar  = $catatan ?: 'Transfer ke ' . $receiver['nama'];
    $ket_masuk   = 'Terima dari ' . $sender['nama'] . ($catatan ? ' - ' . $catatan : '');

    // Catat transaksi keluar
    $stmt = $koneksi->prepare("INSERT INTO transaksi (kode_transaksi,user_id,tipe,jumlah,saldo_sebelum,saldo_sesudah,keterangan,status) VALUES (?,?,'transfer_keluar',?,?,?,?,'success')");
    $stmt->bind_param('siddds', $kode_keluar, $from_id, $jumlah, $saldo_from_before, $saldo_from_after, $ket_keluar);
    $stmt->execute();

    // Catat transaksi masuk
    $stmt2 = $koneksi->prepare("INSERT INTO transaksi (kode_transaksi,user_id,tipe,jumlah,saldo_sebelum,saldo_sesudah,keterangan,status) VALUES (?,?,'transfer_masuk',?,?,?,?,'success')");
    $stmt2->bind_param('siddds', $kode_masuk, $to_id, $jumlah, $saldo_to_before, $saldo_to_after, $ket_masuk);
    $stmt2->execute();

    // Notifikasi penerima
    $jumlah_fmt = 'Rp ' . number_format($jumlah,0,',','.');
    $msg_masuk = "Anda menerima transfer $jumlah_fmt dari {$sender['nama']}" . ($catatan ? ". Ket: $catatan" : "");
    $stmt3 = $koneksi->prepare("INSERT INTO notifications (user_id,judul,pesan,tipe) VALUES (?,?,?,'transfer')");
    $judul = 'Saldo Masuk';
    $stmt3->bind_param('iss', $to_id, $judul, $msg_masuk);
    $stmt3->execute();

    // Log
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $detail = "Transfer $jumlah_fmt ke {$receiver['nama']} (ID:$to_id)";
    $koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('user',$from_id,'Transfer','$detail','$ip')");

    $koneksi->commit();

    // Update session saldo
    $_SESSION['user']['saldo'] = $saldo_from_after;

    echo json_encode(['success'=>true,'kode'=>$kode_keluar,'saldo_baru'=>$saldo_from_after]);

} catch (Exception $e) {
    $koneksi->rollback();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>
