<?php
// =============================================
// JAXPAY - Database Connection
// =============================================

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'jaxpay_db');

define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_FROM', getenv('SMTP_FROM') ?: 'noreply@jaxpay.id');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'JAXPAY Digital Wallet');

define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/jaxpay/');
define('UPLOAD_DIR', __DIR__ . '/assets/uploads/');

$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($koneksi->connect_error) {
    die('<div style="text-align:center;padding:50px;font-family:sans-serif;">
        <h2 style="color:#ef4444;">❌ Database Connection Failed</h2>
        <p>Please make sure MySQL is running and the database <strong>jaxpay_db</strong> exists.</p>
        <p>Import: <code>database/jaxpay.sql</code></p>
        <small>Error: ' . $koneksi->connect_error . '</small>
    </div>');
}

$koneksi->set_charset('utf8mb4');

function sanitize($data) {
    global $koneksi;
    return $koneksi->real_escape_string(htmlspecialchars(strip_tags(trim($data))));
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function generateKode($prefix) {
    return $prefix . '-' . date('Y') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function generateMemberID($role) {
    $prefix = ['student'=>'JAX-STD','teacher'=>'JAX-GTK','parent'=>'JAX-OT','merchant'=>'JAX-MCH'];
    return ($prefix[$role] ?? 'JAX-USR') . rand(10000, 99999);
}
?>
