<?php
session_start();
header('Content-Type: application/json');
require_once '../koneksi.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Sesi habis']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid']); exit; }

$buyer_id  = (int)$_SESSION['user_id'];
$jumlah    = (float)($_POST['jumlah'] ?? 0);
$catatan   = sanitize($_POST['catatan'] ?? '');
$member_id = sanitize($_POST['member_id'] ?? '');
$qr_raw    = $_POST['qr_data'] ?? '';

if ($jumlah < 1000) { echo json_encode(['success'=>false,'message'=>'Nominal minimal Rp 1.000']); exit; }

// Resolve target user
$target = null;

if ($member_id) {
    // Manual by member_id
    $stmt = $koneksi->prepare("SELECT id, nama, saldo, role FROM users WHERE member_id=? AND is_active=1");
    $stmt->bind_param('s', $member_id);
    $stmt->execute();
    $target = $stmt->get_result()->fetch_assoc();
} elseif ($qr_raw) {
    try {
        $qr_data = json_decode($qr_raw, true);
        if (isset($qr_data['user_id'])) {
            $uid = (int)$qr_data['user_id'];
            $target = $koneksi->query("SELECT id, nama, saldo, role FROM users WHERE id=$uid AND is_active=1")->fetch_assoc();
        } elseif (isset($qr_data['member_id'])) {
            $mid = sanitize($qr_data['member_id']);
            $stmt = $koneksi->prepare("SELECT id, nama, saldo, role FROM users WHERE member_id=? AND is_active=1");
            $stmt->bind_param('s', $mid);
            $stmt->execute();
            $target = $stmt->get_result()->fetch_assoc();
        }
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Data QR tidak valid']); exit;
    }
}

if (!$target) { echo json_encode(['success'=>false,'message'=>'Penerima tidak ditemukan atau tidak aktif']); exit; }
if ($target['id'] === $buyer_id) { echo json_encode(['success'=>false,'message'=>'Tidak bisa bayar ke diri sendiri']); exit; }

$koneksi->begin_transaction();
try {
    $buyer = $koneksi->query("SELECT saldo, nama FROM users WHERE id=$buyer_id FOR UPDATE")->fetch_assoc();
    if ($buyer['saldo'] < $jumlah) throw new Exception('Saldo tidak mencukupi');

    $receiver = $koneksi->query("SELECT saldo FROM users WHERE id={$target['id']} FOR UPDATE")->fetch_assoc();

    $buyer_before    = $buyer['saldo'];
    $buyer_after     = $buyer_before - $jumlah;
    $receiver_before = $receiver['saldo'];
    $receiver_after  = $receiver_before + $jumlah;

    $koneksi->query("UPDATE users SET saldo=$buyer_after WHERE id=$buyer_id");
    $koneksi->query("UPDATE users SET saldo=$receiver_after WHERE id={$target['id']}");

    // If merchant, update stats
    $merchant = $koneksi->query("SELECT id FROM merchant WHERE user_id={$target['id']}")->fetch_assoc();
    if ($merchant) {
        $koneksi->query("UPDATE merchant SET total_transaksi=total_transaksi+1, total_omzet=total_omzet+$jumlah WHERE id={$merchant['id']}");
    }

    $kode_buyer = generateKode('TRX');
    $ket_buyer  = $catatan ?: 'QR Payment ke ' . $target['nama'];
    $tipe_buyer = $target['role'] === 'merchant' ? 'pembayaran' : 'transfer_keluar';

    $stmt = $koneksi->prepare("INSERT INTO transaksi (kode_transaksi,user_id,tipe,jumlah,saldo_sebelum,saldo_sesudah,keterangan,status) VALUES (?,?,?,?,?,?,?,'success')");
    $stmt->bind_param('ssiddds', $kode_buyer, $buyer_id, $tipe_buyer, $jumlah, $buyer_before, $buyer_after, $ket_buyer);
    $stmt->execute();

    $kode_recv = generateKode('TRX');
    $ket_recv  = 'Terima QR Payment dari ' . $buyer['nama'];
    $tipe_recv = $target['role'] === 'merchant' ? 'qr_payment' : 'transfer_masuk';

    $stmt2 = $koneksi->prepare("INSERT INTO transaksi (kode_transaksi,user_id,tipe,jumlah,saldo_sebelum,saldo_sesudah,keterangan,status) VALUES (?,?,?,?,?,?,?,'success')");
    $stmt2->bind_param('ssiddds', $kode_recv, $target['id'], $tipe_recv, $jumlah, $receiver_before, $receiver_after, $ket_recv);
    $stmt2->execute();

    // Notification
    $jfmt = 'Rp ' . number_format($jumlah,0,',','.');
    $msg_notif = "Menerima $jfmt dari {$buyer['nama']} via QR Payment.";
    $stmt3 = $koneksi->prepare("INSERT INTO notifications (user_id,judul,pesan,tipe) VALUES (?,'Saldo Masuk',?,'pembayaran')");
    $stmt3->bind_param('is', $target['id'], $msg_notif);
    $stmt3->execute();

    $koneksi->commit();
    $_SESSION['user']['saldo'] = $buyer_after;

    echo json_encode(['success'=>true,'kode'=>$kode_buyer,'nama'=>$target['nama'],'saldo_baru'=>$buyer_after]);

} catch (Exception $e) {
    $koneksi->rollback();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>
