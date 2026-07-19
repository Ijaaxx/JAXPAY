<?php
session_start();
header('Content-Type: application/json');
require_once '../koneksi.php';
if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

$action   = sanitize($_POST['action'] ?? '');
$admin_id = (int)$_SESSION['admin_id'];
$ip       = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

switch ($action) {
  case 'add':
    $user_id   = (int)($_POST['user_id'] ?? 0);
    $nama_toko = sanitize($_POST['nama_toko'] ?? '');
    $kategori  = sanitize($_POST['kategori'] ?? 'Lainnya');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    if (!$user_id || !$nama_toko) { echo json_encode(['success'=>false,'message'=>'Data tidak lengkap']); break; }
    $exist = $koneksi->query("SELECT id FROM merchant WHERE user_id=$user_id")->fetch_assoc();
    if ($exist) { echo json_encode(['success'=>false,'message'=>'User ini sudah memiliki merchant']); break; }
    $stmt = $koneksi->prepare("INSERT INTO merchant (user_id,nama_toko,deskripsi,kategori) VALUES (?,?,?,?)");
    $stmt->bind_param('isss', $user_id, $nama_toko, $deskripsi, $kategori);
    if ($stmt->execute()) {
      $koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('admin',$admin_id,'Tambah Merchant','Tambah merchant: $nama_toko','$ip')");
      echo json_encode(['success'=>true]);
    } else echo json_encode(['success'=>false,'message'=>'Gagal: '.$koneksi->error]);
    break;

  case 'edit':
    $id        = (int)($_POST['merchant_id'] ?? 0);
    $nama_toko = sanitize($_POST['nama_toko'] ?? '');
    $kategori  = sanitize($_POST['kategori'] ?? 'Lainnya');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    if (!$id || !$nama_toko) { echo json_encode(['success'=>false,'message'=>'Data tidak lengkap']); break; }
    $stmt = $koneksi->prepare("UPDATE merchant SET nama_toko=?,kategori=?,deskripsi=? WHERE id=?");
    $stmt->bind_param('sssi', $nama_toko, $kategori, $deskripsi, $id);
    if ($stmt->execute()) echo json_encode(['success'=>true]);
    else echo json_encode(['success'=>false,'message'=>'Gagal update']);
    break;

  case 'toggle':
    $id     = (int)($_POST['merchant_id'] ?? 0);
    $active = (int)($_POST['active'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID tidak valid']); break; }
    if ($koneksi->query("UPDATE merchant SET is_active=$active WHERE id=$id")) {
      $koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('admin',$admin_id,'Toggle Merchant','Merchant ID $id: ".($active?'Aktifkan':'Nonaktifkan')."','$ip')");
      echo json_encode(['success'=>true]);
    } else echo json_encode(['success'=>false,'message'=>'Gagal update status']);
    break;

  default:
    echo json_encode(['success'=>false,'message'=>'Aksi tidak dikenali']);
}
?>
