<?php
session_start();
header('Content-Type: application/json');
require_once '../koneksi.php';
if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

$action   = sanitize($_POST['action'] ?? '');
$admin_id = (int)$_SESSION['admin_id'];

switch ($action) {
  case 'update_profile':
    $nama  = sanitize($_POST['nama'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    if (!$nama || !$email) { echo json_encode(['success'=>false,'message'=>'Nama dan email wajib diisi']); break; }
    $stmt = $koneksi->prepare("UPDATE admin SET nama=?, email=? WHERE id=?");
    $stmt->bind_param('ssi', $nama, $email, $admin_id);
    if ($stmt->execute()) {
      $_SESSION['admin_nama'] = $nama;
      echo json_encode(['success'=>true]);
    } else echo json_encode(['success'=>false,'message'=>'Gagal update profil']);
    break;

  case 'change_password':
    $new_pass = $_POST['new_password'] ?? '';
    if (strlen($new_pass) < 6) { echo json_encode(['success'=>false,'message'=>'Password minimal 6 karakter']); break; }
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $stmt = $koneksi->prepare("UPDATE admin SET password=? WHERE id=?");
    $stmt->bind_param('si', $hashed, $admin_id);
    if ($stmt->execute()) echo json_encode(['success'=>true]);
    else echo json_encode(['success'=>false,'message'=>'Gagal ganti password']);
    break;

  case 'clear_logs':
    if ($koneksi->query("TRUNCATE TABLE activity_logs")) echo json_encode(['success'=>true]);
    else echo json_encode(['success'=>false,'message'=>'Gagal hapus log']);
    break;

  case 'clear_notifs':
    if ($koneksi->query("TRUNCATE TABLE notifications")) echo json_encode(['success'=>true]);
    else echo json_encode(['success'=>false,'message'=>'Gagal hapus notifikasi']);
    break;

  default:
    echo json_encode(['success'=>false,'message'=>'Aksi tidak dikenali']);
}
?>
