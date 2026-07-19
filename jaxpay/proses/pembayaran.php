<?php
session_start();
header('Content-Type: application/json');
require_once '../koneksi.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Sesi habis']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid']); exit; }

$user_id     = (int)$_SESSION['user_id'];
$merchant_id = (int)($_POST['merchant_id'] ?? 0);
$jumlah      = (float)($_POST['jumlah'] ?? 0);
$catatan     = sanitize($_POST['catatan'] ?? '');

if ($merchant_id <= 0 || $jumlah < 1000) {
    echo json_encode(['success'=>false,'message'=>'Data pembayaran tidak valid']);
    exit;
}

$koneksi->begin_transaction();
try {
    $buyer = $koneksi->query("SELECT id,nama,saldo FROM users WHERE id=$user_id FOR UPDATE")->fetch_assoc();
    if (!$buyer || $buyer['saldo'] < $jumlah) throw new Exception('Saldo tidak mencukupi');

    $merchant = $koneksi->query("SELECT m.id, m.user_id, m.nama_toko, u.saldo as msaldo FROM merchant m JOIN users u ON m.user_id=u.id WHERE m.id=$merchant_id AND m.is_active=1 FOR UPDATE")->fetch_assoc();
    if (!$merchant) throw new Exception('Merchant tidak ditemukan');

    $buyer_before   = $buyer['saldo'];
    $buyer_after    = $buyer_before - $jumlah;
    $merchant_after = $merchant['msaldo'] + $jumlah;

    // Potong saldo buyer
    $koneksi->query("UPDATE users SET saldo=$buyer_after WHERE id=$user_id");
    // Tambah saldo merchant
    $koneksi->query("UPDATE users SET saldo=$merchant_after WHERE id={$merchant['user_id']}");
    // Update stats merchant
    $koneksi->query("UPDATE merchant SET total_transaksi=total_transaksi+1, total_omzet=total_omzet+$jumlah WHERE id=$merchant_id");

    $kode = generateKode('TRX');
    $ket  = $catatan ?: 'Bayar di ' . $merchant['nama_toko'];

    $stmt = $koneksi->prepare("INSERT INTO transaksi (kode_transaksi,user_id,tipe,jumlah,saldo_sebelum,saldo_sesudah,keterangan,status) VALUES (?,$user_id,'pembayaran',?,?,?,?,'success')");
    $stmt->bind_param('sddds', $kode, $jumlah, $buyer_before, $buyer_after, $ket);
    $stmt->execute();

    // Catat juga untuk merchant (sebagai pemasukan)
    $kode_m = generateKode('TRX');
    $ket_m  = 'Pembayaran dari ' . $buyer['nama'];
    $stmt2 = $koneksi->prepare("INSERT INTO transaksi (kode_transaksi,user_id,tipe,jumlah,saldo_sebelum,saldo_sesudah,keterangan,status) VALUES (?,?,'qr_payment',?,?,?,?,'success')");
    $stmt2->bind_param('siddds', $kode_m, $merchant['user_id'], $jumlah, $merchant['msaldo'], $merchant_after, $ket_m);
    $stmt2->execute();

    $koneksi->commit();
    $_SESSION['user']['saldo'] = $buyer_after;
    echo json_encode(['success'=>true,'kode'=>$kode,'saldo_baru'=>$buyer_after]);

} catch (Exception $e) {
    $koneksi->rollback();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>
