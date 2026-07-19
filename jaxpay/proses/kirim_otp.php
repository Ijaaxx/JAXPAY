<?php
require_once __DIR__ . '/../../vendor/autoload.php';
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

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Format email tidak valid']);
}

// Check user exists
$stmt = $koneksi->prepare("SELECT id, nama, is_active FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['success'=>false,'message'=>'Email tidak terdaftar di sistem JAXPAY.']);
    exit;
}
if (!$user['is_active']) {
    echo json_encode(['success'=>false,'message'=>'Akun Anda telah dinonaktifkan. Hubungi Admin.']);
    exit;
}

// Invalidate previous OTPs
$koneksi->query("UPDATE otp_codes SET is_used=1 WHERE user_id={$user['id']} AND is_used=0");

// Generate OTP
$otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', time() + 300); // 5 minutes

$stmt2 = $koneksi->prepare("INSERT INTO otp_codes (user_id, email, kode, expires_at) VALUES (?, ?, ?, ?)");
$stmt2->bind_param('isss', $user['id'], $email, $otp, $expires);

if (!$stmt2->execute()) {
    jsonResponse(['success' => false, 'message' => 'Gagal menyimpan OTP. Coba lagi.']);
}

// ======================================================
// SEND EMAIL via PHPMailer
// ======================================================
// Untuk DEMO: tampilkan OTP di response jika email gagal terkirim
$emailSent = false;
$errorMsg = '';
$phpmailerLoaded = false;

$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
$phpmailerPath = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    $phpmailerLoaded = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
} elseif (file_exists($phpmailerPath)) {
    require_once $phpmailerPath;
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
    $phpmailerLoaded = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
}

if ($phpmailerLoaded && SMTP_HOST && SMTP_USER && SMTP_PASS && SMTP_FROM) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email, $user['nama']);
        $mail->Subject = 'Kode OTP Login JAXPAY - ' . $otp;
        $mail->isHTML(true);
        $mail->Body = '
        <div style="font-family:sans-serif;max-width:500px;margin:0 auto;background:#0D0D1A;color:#fff;border-radius:16px;overflow:hidden">
          <div style="background:linear-gradient(135deg,#6C3CE1,#00D4FF);padding:30px;text-align:center">
            <h1 style="margin:0;font-size:28px;letter-spacing:2px">⚡ JAXPAY</h1>
            <p style="margin:8px 0 0;opacity:0.8;font-size:14px">School Digital Wallet</p>
          </div>
          <div style="padding:32px">
            <p style="font-size:16px;margin:0 0 8px">Halo, <strong>' . htmlspecialchars($user['nama']) . '</strong>!</p>
            <p style="color:rgba(255,255,255,0.7);font-size:14px;margin:0 0 24px">Gunakan kode OTP berikut untuk login ke akun JAXPAY Anda:</p>
            <div style="background:rgba(108,60,225,0.2);border:2px solid rgba(108,60,225,0.5);border-radius:16px;padding:24px;text-align:center;margin:0 0 24px">
              <div style="font-size:42px;font-weight:900;letter-spacing:8px;color:#9B72EF">' . $otp . '</div>
              <div style="font-size:12px;color:rgba(255,255,255,0.5);margin-top:8px">Berlaku selama <strong>5 menit</strong></div>
            </div>
            <p style="font-size:12px;color:rgba(255,255,255,0.4);text-align:center">Jangan bagikan kode ini kepada siapapun.<br>JAXPAY tidak pernah meminta kode OTP Anda.</p>
          </div>
        </div>';

        $mail->send();
$emailSent = true;
    } catch (Exception $e) {

    jsonResponse([
        'success' => false,
        'message' => 'SMTP Error',
        'error' => $e->getMessage()
    ]);
}
} else {
    if (!$phpmailerLoaded) {
        $errorMsg = 'PHPMailer tidak ditemukan. Silakan instal via Composer atau letakkan library di vendor/phpmailer/phpmailer.';
    }
}

// Log activity
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('user',{$user['id']},'Request OTP','OTP dikirim ke $email','$ip')");

// Response
$response = ['success' => true, 'message' => 'OTP berhasil dikirim'];


jsonResponse($response);
?>
