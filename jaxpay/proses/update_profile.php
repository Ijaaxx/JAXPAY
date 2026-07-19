<?php
session_start();
header('Content-Type: application/json');
require_once '../koneksi.php';

if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Sesi habis']); exit; }

$user_id = (int)$_SESSION['user_id'];
$nama    = sanitize($_POST['nama'] ?? '');
$no_hp   = sanitize($_POST['no_hp'] ?? '');

if (!$nama) { echo json_encode(['success'=>false,'message'=>'Nama tidak boleh kosong']); exit; }

$foto_filename = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['foto'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png'])) {
        echo json_encode(['success'=>false,'message'=>'Format foto tidak valid (JPG/PNG)']); exit;
    }
    if ($file['size'] > 2*1024*1024) {
        echo json_encode(['success'=>false,'message'=>'Ukuran foto maksimal 2MB']); exit;
    }
    $foto_filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
    move_uploaded_file($file['tmp_name'], UPLOAD_DIR . 'profile/' . $foto_filename);
}

if ($foto_filename) {
    $stmt = $koneksi->prepare("UPDATE users SET nama=?, no_hp=?, foto=? WHERE id=?");
    $stmt->bind_param('sssi', $nama, $no_hp, $foto_filename, $user_id);
} else {
    $stmt = $koneksi->prepare("UPDATE users SET nama=?, no_hp=? WHERE id=?");
    $stmt->bind_param('ssi', $nama, $no_hp, $user_id);
}

if ($stmt->execute()) {
    $updated = $koneksi->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
    $_SESSION['user'] = $updated;
    $_SESSION['user_nama'] = $nama;
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>'Gagal memperbarui profil']);
}
?>
