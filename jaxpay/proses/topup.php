<?php
session_start();
header('Content-Type: application/json');
require_once '../koneksi.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Sesi habis']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid']); exit; }

$user_id = (int)$_SESSION['user_id'];
$nominal = (float)($_POST['nominal'] ?? 0);
$bank    = sanitize($_POST['bank'] ?? '');
$catatan = sanitize($_POST['catatan'] ?? '');

if ($nominal < 10000) { echo json_encode(['success'=>false,'message'=>'Minimal top up Rp 10.000']); exit; }
if (!$bank) { echo json_encode(['success'=>false,'message'=>'Pilih bank tujuan']); exit; }

// Handle upload
$bukti_filename = '';
if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success'=>false,'message'=>'Upload bukti transfer gagal. Coba lagi.']);
    exit;
}

$file = $_FILES['bukti'];
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','gif'];
if (!in_array($ext, $allowed)) { echo json_encode(['success'=>false,'message'=>'Format file tidak didukung. Gunakan JPG/PNG.']); exit; }
if ($file['size'] > 2*1024*1024) { echo json_encode(['success'=>false,'message'=>'Ukuran file maksimal 2MB']); exit; }

$bukti_filename = 'topup_' . $user_id . '_' . time() . '.' . $ext;
$upload_path    = UPLOAD_DIR . 'topup/' . $bukti_filename;

if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    echo json_encode(['success'=>false,'message'=>'Gagal menyimpan file. Periksa permission folder.']);
    exit;
}

$kode = generateKode('TPU');

$stmt = $koneksi->prepare("INSERT INTO topup (kode_topup,user_id,jumlah,metode_bayar,bukti_bayar,catatan,status) VALUES (?,?,?,?,?,?,'pending')");
$metode = $bank . ' Transfer';
$stmt->bind_param('sidsss', $kode, $user_id, $nominal, $metode, $bukti_filename, $catatan);

if ($stmt->execute()) {
    // Notif user
    $jfmt = 'Rp ' . number_format($nominal,0,',','.');
    $stmt2 = $koneksi->prepare("INSERT INTO notifications (user_id,judul,pesan,tipe) VALUES (?,'Top Up Diajukan',?,'topup')");
    $msg = "Pengajuan top up $jfmt via $bank sedang menunggu konfirmasi admin.";
    $stmt2->bind_param('is', $user_id, $msg);
    $stmt2->execute();

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('user',$user_id,'Top Up','Ajukan top up $jfmt via $bank ($kode)','$ip')");

    echo json_encode(['success'=>true,'kode'=>$kode]);
} else {
    unlink($upload_path);
    echo json_encode(['success'=>false,'message'=>'Gagal menyimpan pengajuan. Coba lagi.']);
}
?>
