<?php
session_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ob_start();

function jsonResponse($payload) {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

require_once '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method tidak valid']);
}

$email = sanitize($_POST['email'] ?? '');
$kode  = sanitize($_POST['kode'] ?? '');

if (!$email || !$kode) {
    jsonResponse(['success' => false, 'message' => 'Data tidak lengkap']);
}

// Get user
$stmt = $koneksi->prepare("SELECT id, nama, is_active FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    jsonResponse(['success' => false, 'message' => 'User tidak ditemukan']);
}
if (!$user['is_active']) {
    jsonResponse(['success' => false, 'message' => 'Akun nonaktif. Hubungi Admin.']);
}

// Validate OTP
$now = date('Y-m-d H:i:s');
$stmt2 = $koneksi->prepare("SELECT id FROM otp_codes WHERE user_id=? AND kode=? AND is_used=0 AND expires_at > ?");
$stmt2->bind_param('iss', $user['id'], $kode, $now);
$stmt2->execute();
$otp_row = $stmt2->get_result()->fetch_assoc();

if (!$otp_row) {
    jsonResponse(['success' => false, 'message' => 'Kode OTP salah atau sudah kadaluarsa.']);
}

// Mark OTP as used
$koneksi->query("UPDATE otp_codes SET is_used=1 WHERE id={$otp_row['id']}");

// Get full user data for session
$full_user = $koneksi->query("SELECT * FROM users WHERE id={$user['id']}")->fetch_assoc();

// Set session
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_nama'] = $user['nama'];
$_SESSION['user']      = $full_user;

// Log
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('user',{$user['id']},'Login','Login berhasil via OTP','$ip')");

jsonResponse(['success' => true, 'nama' => $user['nama']]);
?>
