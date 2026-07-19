<?php
session_start();
header('Content-Type: application/json');
require_once '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}

$username = sanitize($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(['success'=>false,'message'=>'Username dan password wajib diisi']);
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if (!$admin) {
    echo json_encode(['success'=>false,'message'=>'Username tidak ditemukan']);
    exit;
}

// Support both hashed and plain password (for demo: 'password')
$valid = false;
if (password_verify($password, $admin['password'])) {
    $valid = true;
} elseif ($password === 'password' && $admin['password'] === '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') {
    // Laravel default hash for 'password' - commonly used in demos
    $valid = true;
} elseif ($password === $admin['password']) {
    // plain text fallback
    $valid = true;
}

if (!$valid) {
    echo json_encode(['success'=>false,'message'=>'Password salah']);
    exit;
}

$_SESSION['admin_id']   = $admin['id'];
$_SESSION['admin_nama'] = $admin['nama'];
$_SESSION['admin']      = $admin;

$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('admin',{$admin['id']},'Login Admin','Login ke dashboard admin','$ip')");

echo json_encode(['success'=>true,'nama'=>$admin['nama']]);
?>
