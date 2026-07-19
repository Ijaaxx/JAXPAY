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
    $nama   = sanitize($_POST['nama'] ?? '');
    $email  = sanitize($_POST['email'] ?? '');
    $no_hp  = sanitize($_POST['no_hp'] ?? '');
    $role   = sanitize($_POST['role'] ?? 'student');
    $nis    = sanitize($_POST['nis_nim'] ?? '');
    $kelas  = sanitize($_POST['kelas'] ?? '');
    $saldo  = max(0, (float)($_POST['saldo'] ?? 0));

    if (!$nama || !$email) { echo json_encode(['success'=>false,'message'=>'Nama dan email wajib diisi']); break; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['success'=>false,'message'=>'Format email tidak valid']); break; }

    $check = $koneksi->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param('s', $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) { echo json_encode(['success'=>false,'message'=>'Email sudah terdaftar']); break; }

    $member_id = generateMemberID($role);
    $stmt = $koneksi->prepare("INSERT INTO users (nama,email,no_hp,nis_nim,kelas,role,saldo,member_id) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param('ssssssds', $nama, $email, $no_hp, $nis, $kelas, $role, $saldo, $member_id);
    if ($stmt->execute()) {
      $uid = $koneksi->insert_id;
      $koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('admin',$admin_id,'Tambah User','Menambah user $nama ($email)','$ip')");
      echo json_encode(['success'=>true,'user_id'=>$uid]);
    } else {
      echo json_encode(['success'=>false,'message'=>'Gagal menyimpan user: '.$koneksi->error]);
    }
    break;

  case 'edit':
    $user_id = (int)($_POST['user_id'] ?? 0);
    $nama    = sanitize($_POST['nama'] ?? '');
    $no_hp   = sanitize($_POST['no_hp'] ?? '');
    $role    = sanitize($_POST['role'] ?? 'student');
    $kelas   = sanitize($_POST['kelas'] ?? '');
    $level   = sanitize($_POST['member_level'] ?? 'Basic');
    $active  = (int)($_POST['is_active'] ?? 1);

    if (!$user_id || !$nama) { echo json_encode(['success'=>false,'message'=>'Data tidak lengkap']); break; }
    $stmt = $koneksi->prepare("UPDATE users SET nama=?,no_hp=?,role=?,kelas=?,member_level=?,is_active=? WHERE id=?");
    $stmt->bind_param('sssssii', $nama, $no_hp, $role, $kelas, $level, $active, $user_id);
    if ($stmt->execute()) {
      $koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('admin',$admin_id,'Edit User','Update data user ID $user_id','$ip')");
      echo json_encode(['success'=>true]);
    } else {
      echo json_encode(['success'=>false,'message'=>'Gagal update: '.$koneksi->error]);
    }
    break;

  case 'delete':
    $user_id = (int)($_POST['user_id'] ?? 0);
    if (!$user_id) { echo json_encode(['success'=>false,'message'=>'User ID tidak valid']); break; }
    $user = $koneksi->query("SELECT nama FROM users WHERE id=$user_id")->fetch_assoc();
    if (!$user) { echo json_encode(['success'=>false,'message'=>'User tidak ditemukan']); break; }
    if ($koneksi->query("DELETE FROM users WHERE id=$user_id")) {
      $koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('admin',$admin_id,'Hapus User','Menghapus user {$user['nama']} (ID $user_id)','$ip')");
      echo json_encode(['success'=>true]);
    } else {
      echo json_encode(['success'=>false,'message'=>'Gagal menghapus']);
    }
    break;

  case 'reset_saldo':
    $user_id   = (int)($_POST['user_id'] ?? 0);
    $new_saldo = max(0, (float)($_POST['saldo'] ?? 0));
    if (!$user_id) { echo json_encode(['success'=>false,'message'=>'User ID tidak valid']); break; }
    if ($koneksi->query("UPDATE users SET saldo=$new_saldo WHERE id=$user_id")) {
      $jfmt = number_format($new_saldo,0,',','.');
      $koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('admin',$admin_id,'Reset Saldo','Saldo user ID $user_id diset ke Rp $jfmt','$ip')");
      echo json_encode(['success'=>true]);
    } else {
      echo json_encode(['success'=>false,'message'=>'Gagal update saldo']);
    }
    break;

  default:
    echo json_encode(['success'=>false,'message'=>'Aksi tidak dikenali']);
}
?>
