<?php
session_start();
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
if (!$email) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Verifikasi OTP</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  :root {
    --primary: #6C3CE1; --accent: #00D4FF;
    --dark: #0D0D1A; --border: rgba(255,255,255,0.12);
    --text-muted: rgba(255,255,255,0.6);
  }
  body { background:#0D0D1A; min-height:100vh; display:flex; align-items:center; justify-content:center;
    font-family:'Segoe UI',system-ui,sans-serif;
    background-image: radial-gradient(ellipse 80% 80% at 20% -20%, rgba(108,60,225,0.4) 0%, transparent 60%),
      radial-gradient(ellipse 60% 60% at 80% 110%, rgba(0,212,255,0.2) 0%, transparent 50%);
  }
  .phone-container {
    width: min(390px, 94vw); height: min(844px, 94vh); background:#0A0A15; border-radius:50px; overflow:hidden;
    box-shadow:0 0 0 2px rgba(255,255,255,0.06),0 40px 80px rgba(0,0,0,0.7),0 0 80px rgba(108,60,225,0.25);
    display:flex; flex-direction:column; margin:12px;
  }
  .notch { position:absolute; top:0; left:50%; transform:translateX(-50%); width:126px; height:37px;
    background:#000; border-radius:0 0 20px 20px; z-index:100; display:flex; align-items:center;
    justify-content:center; gap:8px; }
  .notch-cam { width:12px; height:12px; border-radius:50%; background:#1a1a1a; border:2px solid #333; }
  .notch-sensor { width:30px; height:6px; border-radius:3px; background:#1a1a1a; }
  .phone-container { position:relative; }
  .status-bar { height:50px; display:flex; align-items:flex-end; justify-content:space-between;
    padding:0 28px 8px; font-size:12px; font-weight:600; color:#fff; flex-shrink:0; }
  .status-icons { display:flex; gap:6px; align-items:center; }

  .otp-content { flex:1; overflow-y:auto; padding:20px 28px 40px; scrollbar-width:none; }
  .otp-content::-webkit-scrollbar { display:none; }

  .back-btn { display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.6);
    font-size:14px; cursor:pointer; margin-bottom:24px; background:none; border:none; }
  .back-btn:hover { color:#fff; }

  .otp-header { text-align:center; margin-bottom:32px; animation:fadeInDown 0.6s ease; }
  .otp-icon { width:80px; height:80px; background:linear-gradient(135deg,#6C3CE1,#00D4FF);
    border-radius:24px; display:flex; align-items:center; justify-content:center;
    margin:0 auto 20px; font-size:36px; color:#fff; box-shadow:0 8px 32px rgba(108,60,225,0.5); }
  .otp-header h2 { font-size:22px; font-weight:700; color:#fff; margin-bottom:8px; }
  .otp-header p { color:var(--text-muted); font-size:13px; }
  .email-display { color:#00D4FF; font-weight:600; }

  .otp-card { background:rgba(255,255,255,0.05); border:1px solid var(--border);
    border-radius:24px; padding:28px 24px; backdrop-filter:blur(20px); animation:fadeInUp 0.7s ease 0.2s both; }

  .otp-inputs { display:flex; gap:10px; justify-content:center; margin-bottom:28px; }
  .otp-input {
    width:48px; height:58px; min-width:40px; background:rgba(255,255,255,0.07); border:2px solid var(--border);
    border-radius:14px; font-size:24px; font-weight:700; color:#fff; text-align:center;
    outline:none; transition:all 0.18s; caret-color:transparent;
  }
  .otp-input:focus { border-color:#6C3CE1; background:rgba(108,60,225,0.15);
    box-shadow:0 0 0 3px rgba(108,60,225,0.2); transform:scale(1.05); }
  .otp-input.filled { border-color:#00D4FF; background:rgba(0,212,255,0.1); }

  .btn-verify { width:100%; background:linear-gradient(135deg,#6C3CE1,#9B72EF); border:none;
    border-radius:14px; padding:16px; font-size:16px; font-weight:700; color:#fff; cursor:pointer;
    transition:all 0.3s; }
  .btn-verify:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(108,60,225,0.5); }
  .btn-verify:disabled { opacity:0.5; cursor:not-allowed; transform:none; }

  .timer-section { text-align:center; margin-top:20px; }
  .timer-text { color:var(--text-muted); font-size:13px; }
  .timer-count { color:#FFB800; font-weight:700; font-size:20px; display:block; margin:8px 0; }
  .resend-btn { background:none; border:none; color:#6C3CE1; font-size:14px; font-weight:600;
    cursor:pointer; display:none; }
  .resend-btn:hover { color:#9B72EF; text-decoration:underline; }
  .resend-btn.visible { display:block; margin:0 auto; }

  @keyframes fadeInDown { from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)} }
  @keyframes fadeInUp { from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)} }
  @keyframes shake { 0%,100%{transform:translateX(0)} 20%,60%{transform:translateX(-6px)} 40%,80%{transform:translateX(6px)} }
</style>
  <link rel="stylesheet" href="../assets/css/theme.css?v=5">
  <script src="../assets/js/theme.js?v=3" defer></script>
  <script src="../assets/js/otp.js" defer></script>
</head>
<body>
<div class="phone-container">
  <div class="notch">
    <div class="notch-sensor"></div>
    <div class="notch-cam"></div>
  </div>
  <div class="status-bar">
    <span>9:41</span>
    <div class="status-icons">
      <i class="fas fa-signal"></i>
      <i class="fas fa-wifi"></i>
      <i class="fas fa-battery-full"></i>
    </div>
  </div>

  <div class="otp-content">
    <button class="back-btn" onclick="history.back()">
      <i class="fas fa-arrow-left"></i> Kembali
    </button>

    <div class="otp-header">
      <div class="otp-icon"><i class="fas fa-shield-halved"></i></div>
      <h2>Verifikasi OTP</h2>
      <p>Masukkan 6 digit kode yang dikirim ke<br>
      <span class="email-display"><?= $email ?></span></p>
    </div>

    <div class="otp-card">
      <div class="otp-inputs">
        <input type="tel" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input" maxlength="1" data-index="0" aria-label="Digit 1">
        <input type="tel" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input" maxlength="1" data-index="1" aria-label="Digit 2">
        <input type="tel" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input" maxlength="1" data-index="2" aria-label="Digit 3">
        <input type="tel" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input" maxlength="1" data-index="3" aria-label="Digit 4">
        <input type="tel" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input" maxlength="1" data-index="4" aria-label="Digit 5">
        <input type="tel" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" class="otp-input" maxlength="1" data-index="5" aria-label="Digit 6">
      </div>

      <button class="btn-verify" id="btnVerify" disabled>
        <i class="fas fa-check-circle"></i> Verifikasi OTP
      </button>

      <div class="timer-section">
        <p class="timer-text">Kode berlaku selama</p>
        <span class="timer-count" id="timerDisplay">05:00</span>
        <button class="resend-btn" id="resendBtn" onclick="resendOTP()">
          <i class="fas fa-rotate-right"></i> Kirim Ulang OTP
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const email = '<?= addslashes($email) ?>';
  const btnVerify = document.getElementById('btnVerify');
  const resendBtn = document.getElementById('resendBtn');

  // Initialize OTP manager (uses assets/js/otp.js)
  const manager = new OTPManager('.otp-card', 6);
  if (manager.inputs && manager.inputs[0] && manager.inputs[0].focus) manager.inputs[0].focus();

  // Timer
  const otpTimer = new OTPTimer('timerDisplay', 300, () => {
    if (resendBtn) resendBtn.classList.add('visible');
  });
  otpTimer.start();

  // Keep verify button state in sync (OTPManager.onChange already toggles it, but ensure initial state)
  if (btnVerify) btnVerify.disabled = !manager.isComplete();

  // Verify handler
  if (btnVerify) {
    btnVerify.addEventListener('click', async () => {
      const code = manager.getValue();
      btnVerify.disabled = true;
      btnVerify.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Memverifikasi...';

      Swal.fire({ title:'Memverifikasi...', allowOutsideClick:false, didOpen:()=>Swal.showLoading(), background:'#1A1A2E', color:'#fff' });

      try {
        const resp = await fetch('../proses/verify_otp.php', {
          method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: 'email='+encodeURIComponent(email)+'&kode='+encodeURIComponent(code)
        });
        const data = await resp.json();

        if (data.success) {
          Swal.fire({ icon:'success', title:'Berhasil Login!', html:'Selamat datang, <strong>'+ (data.nama||'') +'</strong>!', background:'#1A1A2E', color:'#fff', confirmButtonColor:'#6C3CE1', timer:1800, showConfirmButton:false }).then(()=> window.location.href = '../halaman/home.php');
        } else {
          manager.shake();
          Swal.fire({ icon:'error', title:'Kode Salah', text:data.message||'OTP tidak valid atau sudah kadaluarsa.', background:'#1A1A2E', color:'#fff', confirmButtonColor:'#6C3CE1' });
          btnVerify.disabled = false;
          btnVerify.innerHTML = '<i class="fas fa-check-circle"></i> Verifikasi OTP';
        }
      } catch (err) {
        btnVerify.disabled = false;
        btnVerify.innerHTML = '<i class="fas fa-check-circle"></i> Verifikasi OTP';
        Swal.fire({ icon:'error', title:'Gagal', text:'Terjadi kesalahan jaringan.', background:'#1A1A2E', color:'#fff' });
      }
    });
  }

  // Resend handler (exposed globally via window.resendOTP to keep markup onclick)
  window.resendOTP = async function () {
    if (!resendBtn) return;
    resendBtn.disabled = true;
    try {
      const resp = await fetch('../proses/kirim_otp.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: 'email='+encodeURIComponent(email) });
      const data = await resp.json();
      if (data.success) {
        Swal.fire({ icon:'success', title:'OTP Baru Terkirim!', text:'Periksa email Anda.', background:'#1A1A2E', color:'#fff', confirmButtonColor:'#6C3CE1', timer:1600, showConfirmButton:false });
        otpTimer.reset(300);
        resendBtn.classList.remove('visible');
        resendBtn.disabled = false;
        manager.clear();
      } else {
        Swal.fire({ icon:'error', title:'Gagal', text:data.message||'Tidak dapat mengirim OTP.', background:'#1A1A2E', color:'#fff' });
        resendBtn.disabled = false;
      }
    } catch (err) {
      Swal.fire({ icon:'error', title:'Gagal', text:'Terjadi kesalahan jaringan.', background:'#1A1A2E', color:'#fff' });
      resendBtn.disabled = false;
    }
  };

});
</script>

<!-- Theme toggle removed from public OTP/login screens (show only after authenticated) -->

</body>
</html>
