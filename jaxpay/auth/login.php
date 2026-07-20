<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Login</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  :root {
    --primary: #6C3CE1;
    --primary-dark: #5028C5;
    --primary-light: #9B72EF;
    --accent: #00D4FF;
    --gold: #FFB800;
    --dark: #0D0D1A;
    --surface: #1A1A2E;
    --text: #FFFFFF;
    --text-muted: rgba(255,255,255,0.6);
    --card: rgba(255,255,255,0.08);
    --border: rgba(255,255,255,0.12);
  }
  body {
    background: var(--dark);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', system-ui, sans-serif;
    overflow: hidden;
  }
  /* Animated Background */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background: 
      radial-gradient(ellipse 80% 80% at 20% -20%, rgba(108,60,225,0.4) 0%, transparent 60%),
      radial-gradient(ellipse 60% 60% at 80% 110%, rgba(0,212,255,0.2) 0%, transparent 50%);
    z-index: 0;
  }
  .particles {
    position: fixed;
    inset: 0;
    z-index: 0;
    overflow: hidden;
  }
  .particle {
    position: absolute;
    border-radius: 50%;
    background: rgba(108,60,225,0.3);
    animation: float linear infinite;
  }
  @keyframes float {
    from { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    to { transform: translateY(-100px) rotate(360deg); opacity: 0; }
  }

  /* PHONE FRAME */
  .phone-container {
    position: relative;
    z-index: 10;
    width: min(390px, 94vw);
    height: min(844px, 94vh);
    margin: 12px;
    background: #0A0A15;
    border-radius: 50px;
    overflow: hidden;
    box-shadow:
      0 0 0 2px rgba(255,255,255,0.06),
      0 0 0 8px rgba(255,255,255,0.02),
      0 40px 80px rgba(0,0,0,0.7),
      0 0 80px rgba(108,60,225,0.25);
    display: flex;
    flex-direction: column;
  }
  /* iPhone notch */
  .notch {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 126px;
    height: 37px;
    background: #000;
    border-radius: 0 0 20px 20px;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }
  .notch-cam {
    width: 12px; height: 12px;
    border-radius: 50%;
    background: #1a1a1a;
    border: 2px solid #333;
  }
  .notch-sensor {
    width: 30px; height: 6px;
    border-radius: 3px;
    background: #1a1a1a;
  }
  .status-bar {
    height: 50px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    padding: 0 28px 8px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    flex-shrink: 0;
  }
  .status-icons { display: flex; gap: 6px; align-items: center; }
  .status-icons i { font-size: 11px; }

  /* LOGIN CONTENT */
  .login-content {
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    padding: 20px 28px 40px;
    scrollbar-width: none;
  }
  .login-content::-webkit-scrollbar { display: none; }

  .logo-area {
    text-align: center;
    padding: 30px 0 40px;
    animation: fadeInDown 0.6s ease;
  }
  .logo-icon {
    width: 80px; height: 80px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 24px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
    box-shadow: 0 8px 32px rgba(108,60,225,0.5);
    font-size: 36px;
    color: white;
    position: relative;
  }
  .logo-icon { display: none; }
  .logo-img { width: 80px; height: 80px; border-radius: 20px; display: block; margin: 0 auto 16px; object-fit: contain; }
  .logo-icon::after {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 26px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    z-index: -1;
    filter: blur(10px);
    opacity: 0.6;
  }
  .logo-area h1 {
    font-size: 32px;
    font-weight: 800;
    background: linear-gradient(135deg, #fff, var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: 2px;
  }
  .logo-area p {
    color: var(--text-muted);
    font-size: 13px;
    margin-top: 6px;
    letter-spacing: 1px;
  }

  .login-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 28px 24px;
    backdrop-filter: blur(20px);
    animation: fadeInUp 0.7s ease 0.2s both;
  }
  .login-card h2 {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 6px;
  }
  .login-card p {
    color: var(--text-muted);
    font-size: 13px;
    margin-bottom: 24px;
  }

  .form-group {
    margin-bottom: 18px;
  }
  .form-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
  }
  .form-input {
    width: 100%;
    background: rgba(255,255,255,0.07);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px 16px 14px 46px;
    font-size: 15px;
    color: #fff;
    outline: none;
    transition: all 0.3s;
    font-family: inherit;
  }
  .form-input::placeholder { color: rgba(255,255,255,0.3); }
  .form-input:focus {
    border-color: var(--primary-light);
    background: rgba(108,60,225,0.1);
    box-shadow: 0 0 0 3px rgba(108,60,225,0.2);
  }
  .input-wrapper {
    position: relative;
  }
  .input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary-light);
    font-size: 16px;
  }

  .btn-primary {
    width: 100%;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border: none;
    border-radius: 14px;
    padding: 16px;
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    margin-top: 8px;
    transition: all 0.3s;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
  }
  .btn-primary::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent);
    opacity: 0;
    transition: opacity 0.3s;
  }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(108,60,225,0.5); }
  .btn-primary:hover::before { opacity: 1; }
  .btn-primary:active { transform: translateY(0); }

  .divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 20px 0;
    color: var(--text-muted);
    font-size: 12px;
  }
  .divider::before, .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
  }

  .admin-link {
    text-align: center;
    color: var(--text-muted);
    font-size: 13px;
  }
  .admin-link a {
    color: var(--accent);
    text-decoration: none;
    font-weight: 600;
  }
  .admin-link a:hover { text-decoration: underline; }

  .info-box {
    background: rgba(0,212,255,0.08);
    border: 1px solid rgba(0,212,255,0.2);
    border-radius: 12px;
    padding: 12px 14px;
    margin-top: 16px;
    font-size: 12px;
    color: rgba(0,212,255,0.9);
    display: flex;
    gap: 8px;
    align-items: flex-start;
  }

  @keyframes fadeInDown { from { opacity:0; transform: translateY(-20px); } to { opacity:1; transform: translateY(0); } }
  @keyframes fadeInUp { from { opacity:0; transform: translateY(20px); } to { opacity:1; transform: translateY(0); } }

  /* Demo users hint */
  .demo-hint {
    margin-top: 14px;
    background: rgba(108,60,225,0.1);
    border: 1px solid rgba(108,60,225,0.3);
    border-radius: 12px;
    padding: 12px;
    font-size: 11px;
    color: var(--text-muted);
  }
  .demo-hint strong { color: var(--primary-light); display: block; margin-bottom: 6px; }
  .demo-email {
    cursor: pointer;
    color: var(--accent);
    font-size: 12px;
    padding: 3px 0;
    display: block;
  }
  .demo-email:hover { text-decoration: underline; }
</style>
  <link rel="stylesheet" href="../assets/css/theme.css?v=4">
  <script src="../assets/js/theme.js?v=3" defer></script>
  <script src="../assets/js/login.js" defer></script>
</head>
<body>

<div class="particles" id="particles"></div>

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

  <div class="login-content">
    <div class="logo-area">
      <img src="../assets/img/Logo.png" alt="JAXPAY" class="logo-img">
      <h1>JAXPAY</h1>
      <p>School Digital Wallet</p>
    </div>

    <div class="login-card">
      <h2>Masuk ke JAXPAY</h2>
      <p>Masukkan email terdaftar untuk menerima kode OTP</p>

      <form id="loginForm">
        <div class="form-group">
          <label class="form-label">Email Terdaftar</label>
          <div class="input-wrapper">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" name="email" id="emailInput" class="form-input"
              placeholder="email@sekolah.id" required autocomplete="email">
          </div>
        </div>
        <button type="submit" class="btn-primary" id="btnLogin">
          <i class="fas fa-paper-plane"></i> Kirim Kode OTP
        </button>
      </form>

      <div class="divider">atau</div>
      <div class="admin-link">
        Login sebagai Admin? <a href="../admin/index.php">Dashboard Admin</a>
      </div>

      <div class="info-box">
        <i class="fas fa-info-circle" style="margin-top:2px;flex-shrink:0;"></i>
        <span>Kode OTP akan dikirim ke email Anda dan berlaku selama 5 menit. Akun hanya bisa dibuat oleh Admin.</span>
      </div>
    </div>
  </div>
</div>

<!-- login logic moved to assets/js/login.js -->

<!-- Theme toggle removed from public login screen (show only after authenticated) -->

</body>
</html>
