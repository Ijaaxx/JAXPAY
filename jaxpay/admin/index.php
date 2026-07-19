<?php
session_start();
require_once '../koneksi.php';
if (isset($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit; }
$error = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_error']);
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Admin Login</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--primary:#6C3CE1;--accent:#00D4FF;--dark:#ffffff;--surface:#f8fafc;--border:rgba(15,23,42,0.1)}
body{min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',system-ui,sans-serif;color:#0f172a;
  background:#f4f5ff;
  background-image:radial-gradient(ellipse 70% 70% at 10% 10%,rgba(108,60,225,0.08) 0%,transparent 60%),
  radial-gradient(ellipse 60% 60% at 90% 90%,rgba(0,212,255,0.06) 0%,transparent 55%);
}
.login-wrap{width:100%;max-width:420px;padding:20px}
.login-logo{text-align:center;margin-bottom:36px}
.logo-box{width:72px;height:72px;background:linear-gradient(135deg,#6C3CE1,#00D4FF);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:32px;box-shadow:0 8px 32px rgba(108,60,225,0.4)}
.logo-img{width:72px;height:72px;display:block;margin:0 auto 16px;border-radius:18px;object-fit:contain}
.login-logo h1{font-size:30px;font-weight:800;letter-spacing:2px;background:linear-gradient(135deg,#1a0050,#6C3CE1);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.login-logo p{color:#475569;font-size:13px;margin-top:6px}
.admin-badge{display:inline-block;background:rgba(108,60,225,0.08);border:1px solid rgba(108,60,225,0.2);border-radius:20px;padding:4px 14px;font-size:11px;font-weight:700;color:#6C3CE1;letter-spacing:1px;margin-top:10px}
.login-card{background:#ffffff;border:1px solid var(--border);border-radius:20px;padding:32px;box-shadow:0 12px 40px rgba(15,23,42,0.06)}
.login-card h2{font-size:20px;font-weight:700;margin-bottom:6px;color:#0f172a}
.login-card p{color:#475569;font-size:13px;margin-bottom:28px}
.form-group{margin-bottom:18px}
.form-label{display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px}
.input-wrap{position:relative}
.input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9B72EF;font-size:15px}
.form-input{width:100%;background:#f8fafc;border:1px solid var(--border);border-radius:12px;padding:13px 14px 13px 44px;font-size:14px;color:#0f172a;outline:none;transition:all 0.3s;font-family:inherit}
.form-input::placeholder{color:#94a3b8}
.form-input:focus{border-color:#6C3CE1;background:rgba(108,60,225,0.04);box-shadow:0 0 0 3px rgba(108,60,225,0.12)}
.toggle-pass{position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:14px;background:none;border:none}
.btn-login{width:100%;background:linear-gradient(135deg,#6C3CE1,#9B72EF);border:none;border-radius:12px;padding:15px;font-size:16px;font-weight:700;color:#fff;cursor:pointer;transition:all 0.3s;font-family:inherit;margin-top:4px}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(108,60,225,0.25)}
.btn-login:active{transform:translateY(0)}
.back-link{text-align:center;margin-top:20px;font-size:13px;color:#64748b}
.back-link a{color:#6C3CE1;text-decoration:none;font-weight:600}
.back-link a:hover{text-decoration:underline}
.demo-hint{margin-top:18px;background:rgba(108,60,225,0.06);border:1px solid rgba(108,60,225,0.15);border-radius:12px;padding:12px 14px;font-size:12px;color:#475569}
.demo-hint strong{color:#6C3CE1;display:block;margin-bottom:4px}
/* Custom Error Modal */
.custom-modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(8px);
  z-index: 9999; display: flex; align-items: center; justify-content: center;
  opacity: 0; pointer-events: none; transition: opacity 0.4s ease;
}
.custom-modal-overlay.show { opacity: 1; pointer-events: auto; }
.custom-modal-content {
  background: #ffffff; border: 1px solid rgba(15,23,42,0.1);
  border-radius: 28px; padding: 40px 32px; text-align: center; color: #0f172a; width: 90%; max-width: 340px;
  transform: scale(0.85) translateY(10px); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  box-shadow: 0 20px 50px rgba(15,23,42,0.12);
}
.custom-modal-overlay.show .custom-modal-content { transform: scale(1) translateY(0); }
.modal-icon-x {
  width: 64px; height: 64px; border-radius: 50%; border: 3px solid #EF4444; color: #EF4444;
  display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 20px;
  background: rgba(239, 68, 68, 0.1);
}
.custom-modal-content h3 { font-size: 22px; font-weight: 700; margin-bottom: 8px; letter-spacing: 0.5px; }
.custom-modal-content p { font-size: 14px; color: #475569; margin-bottom: 28px; }
.custom-modal-content button {
  width: 100%; background: #f8fafc; border: 1px solid #e2e8f0;
  color: #0f172a; border-radius: 14px; padding: 14px; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.2s ease;
}
.custom-modal-content button:hover { background: var(--primary); border-color: var(--primary); color: #fff; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(108,60,225,0.25); }
.custom-modal-content button:active { transform: translateY(0); }
</style>
  <link rel="stylesheet" href="../assets/css/theme.css?v=4">
  
</head>
<body>
<div class="login-wrap">
  <div class="login-logo">
    <img src="../assets/img/logo.png" alt="JAXPAY" class="logo-img">
    <h1>JAXPAY</h1>
    <p>School Digital Wallet System</p>
    <div class="admin-badge"><i class="fas fa-shield-halved"></i> ADMIN PANEL</div>
  </div>

  <div class="login-card">
    <h2>Login Administrator</h2>
    <p>Masukkan kredensial admin Anda</p>

    <?php if ($error): ?>
    <div style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:10px 14px;font-size:13px;color:#EF4444;margin-bottom:16px">
      <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form id="adminLoginForm">
      <div class="form-group">
        <label class="form-label">Username</label>
        <div class="input-wrap">
          <i class="fas fa-user input-icon"></i>
          <input type="text" name="username" id="usernameInput" class="form-input" placeholder="Username admin" required autocomplete="username">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-wrap">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" name="password" id="passwordInput" class="form-input" placeholder="••••••••" required autocomplete="current-password">
          <button type="button" class="toggle-pass" onclick="togglePassword()">
            <i class="fas fa-eye" id="eyeToggle"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-login" id="btnLogin">
        <i class="fas fa-right-to-bracket"></i> Masuk ke Dashboard
      </button>
    </form>

  </div>

  <div class="back-link">
    &larr; <a href="../auth/login.php">Kembali ke Login User</a>
  </div>
</div>

<!-- Custom Error Modal -->
<div id="errorModalOverlay" class="custom-modal-overlay">
  <div class="custom-modal-content">
    <div class="modal-icon-x"><i class="fas fa-times"></i></div>
    <h3>Login Gagal</h3>
    <p id="errorModalText">Password salah</p>
    <button onclick="closeErrorModal()">OK</button>
  </div>
</div>

<script>
function togglePassword() {
  const inp = document.getElementById('passwordInput');
  const eye = document.getElementById('eyeToggle');
  inp.type = inp.type === 'password' ? 'text' : 'password';
  eye.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}



let errorModalTimeout = null;

function closeErrorModal() {
  const overlay = document.getElementById('errorModalOverlay');
  overlay.classList.remove('show');
  clearTimeout(errorModalTimeout);
}

document.getElementById('errorModalOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeErrorModal();
});

document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('btnLogin');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Memverifikasi...';

  const username = document.getElementById('usernameInput').value;
  const password = document.getElementById('passwordInput').value;

  Swal.fire({title:'Autentikasi...',allowOutsideClick:false,didOpen:()=>Swal.showLoading(),background:'#fff',color:'#0f172a'});

  try {
    const resp = await fetch('login.php', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    });
    const data = await resp.json();

    if (data.success) {
      Swal.fire({icon:'success',title:'Login Berhasil!',text:'Selamat datang, '+data.nama+'!',
        background:'#fff',color:'#0f172a',timer:1500,showConfirmButton:false})
        .then(()=>window.location.href='dashboard.php');
    } else {
      Swal.close(); // Close the loading swal first!
      
      // Update and show custom error modal
      document.getElementById('errorModalText').textContent = data.message || 'Username atau password salah';
      document.getElementById('errorModalOverlay').classList.add('show');
      
      // Reset button
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-right-to-bracket"></i> Masuk ke Dashboard';
      
      // Auto close modal
      clearTimeout(errorModalTimeout);
      errorModalTimeout = setTimeout(() => {
        closeErrorModal();
      }, 2500); // 2.5 seconds
    }
  } catch (err) {
    Swal.close();
    document.getElementById('errorModalText').textContent = 'Terjadi kesalahan jaringan';
    document.getElementById('errorModalOverlay').classList.add('show');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-right-to-bracket"></i> Masuk ke Dashboard';
    
    clearTimeout(errorModalTimeout);
    errorModalTimeout = setTimeout(closeErrorModal, 2500);
  }
});
</script>


</body>
</html>
