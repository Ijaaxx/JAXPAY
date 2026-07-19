<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
$user_id = $_SESSION['user_id'];
$user = $koneksi->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$qr_data = json_encode([
  'type' => 'jaxpay_user',
  'user_id' => $user_id,
  'member_id' => $user['member_id'],
  'nama' => $user['nama'],
  'ts' => time()
]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - QR Pay</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<link rel="stylesheet" href="../assets/css/mobile.css">
<style>
.qr-tabs {
  display: flex; margin: 16px; gap: 8px;
  background: rgba(255,255,255,0.05); border-radius: 14px; padding: 4px;
}
.qr-tab {
  flex: 1; padding: 10px; border-radius: 10px; border: none;
  font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s;
  background: transparent; color: var(--text-muted); font-family: inherit;
}
.qr-tab.active { background: var(--primary); color: #fff; box-shadow: 0 4px 12px rgba(108,60,225,0.4); }

.qr-panel { display: none; padding: 0 16px; }
.qr-panel.active { display: block; }

.qr-display-card {
  background: #fff; border-radius: 24px; padding: 24px;
  text-align: center; margin-bottom: 16px;
}
.qr-header { font-size: 12px; font-weight: 700; color: #6C3CE1; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; }
#qrcode { display: inline-block; }
#qrcode canvas, #qrcode img { border-radius: 12px !important; }
.qr-footer-text { font-size: 11px; color: #999; margin-top: 12px; }
.qr-name { font-size: 15px; font-weight: 700; color: #222; margin-top: 10px; }
.qr-member { font-size: 12px; color: #888; }

.qr-actions { display: flex; gap: 10px; margin-bottom: 16px; }
.btn-outline {
  flex: 1; background: transparent; border: 1px solid var(--border);
  border-radius: 14px; padding: 12px; color: rgba(255,255,255,0.7);
  font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit;
  display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;
}
.btn-outline:hover { border-color: var(--primary-light); color: var(--primary-light); }

.amount-qr-section { background: var(--card); border: 1px solid var(--border); border-radius: 20px; padding: 20px; margin-bottom: 16px; }
.amount-qr-section h4 { font-size: 14px; font-weight: 700; margin-bottom: 12px; }

.scan-frame {
  background: rgba(0,0,0,0.5); border-radius: 20px; padding: 20px;
  text-align: center; margin-bottom: 16px; min-height: 240px;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  border: 2px dashed var(--border);
}
.scan-icon { font-size: 64px; color: var(--primary-light); margin-bottom: 16px; animation: pulse 2s infinite; }
.scan-text { font-size: 14px; font-weight: 600; color: rgba(255,255,255,0.7); }
.scan-sub { font-size: 12px; color: var(--text-muted); margin-top: 6px; }

.timer-ring { font-size: 13px; color: var(--warning); font-weight: 700; display: flex; align-items: center; gap: 6px; justify-content: center; margin-top: 12px; }
</style>
  <link rel="stylesheet" href="../assets/css/theme.css?v=4">
  <script src="../assets/js/theme.js?v=3"></script>
</head>
<body>
<div class="phone-outer">
<div class="phone-frame">
  <div class="dynamic-island"><div class="di-speaker"></div><div class="di-camera"></div></div>
  <div class="status-bar"><span>9:41</span><div class="status-right"><i class="fas fa-signal"></i><i class="fas fa-wifi"></i><i class="fas fa-battery-full"></i></div></div>

  <div class="phone-content">
    <div class="page-header animate-up">
      <a href="home.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
      <div class="page-title">QR Payment</div>
    </div>

    <!-- Tabs -->
    <div class="qr-tabs animate-up-1">
      <button class="qr-tab active" onclick="switchTab('show')"><i class="fas fa-qrcode"></i> Tampilkan QR</button>
      <button class="qr-tab" onclick="switchTab('scan')"><i class="fas fa-camera"></i> Scan QR</button>
    </div>

    <!-- Show QR Panel -->
    <div class="qr-panel active animate-up-2" id="panel-show">
      <div class="qr-display-card">
        <div class="qr-header"><i class="fas fa-bolt"></i> JAXPAY QR CODE</div>
        <div id="qrcode"></div>
        <div class="qr-name"><?= htmlspecialchars($user['nama']) ?></div>
        <div class="qr-member"><?= $user['member_id'] ?> · <?= ucfirst($user['role']) ?></div>
        <div class="qr-footer-text">Tunjukkan QR ini ke merchant / kasir</div>
      </div>

      <div class="timer-ring">
        <i class="fas fa-clock"></i> QR berlaku: <span id="qrTimer">05:00</span>
      </div>

      <div class="qr-actions" style="margin-top:14px">
        <button class="btn-outline" onclick="refreshQR()"><i class="fas fa-rotate-right"></i> Refresh</button>
        <button class="btn-outline" onclick="shareQR()"><i class="fas fa-share-nodes"></i> Bagikan</button>
      </div>

      <!-- Input nominal for specific amount QR -->
      <div class="amount-qr-section">
        <h4><i class="fas fa-tag"></i> QR dengan Nominal Tertentu</h4>
        <div style="position:relative;margin-bottom:12px">
          <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:700;font-size:14px">Rp</span>
          <input type="number" id="specificAmount" class="form-input" style="padding-left:40px" placeholder="Masukkan nominal...">
        </div>
        <button class="btn-primary" onclick="generateAmountQR()" style="padding:12px">
          <i class="fas fa-qrcode"></i> Generate QR
        </button>
      </div>
    </div>

    <!-- Scan Panel -->
    <div class="qr-panel" id="panel-scan">
      <div class="scan-frame">
        <div class="scan-icon"><i class="fas fa-camera-rotate"></i></div>
        <div class="scan-text">Scan QR Merchant</div>
        <div class="scan-sub">Arahkan kamera ke QR Code merchant</div>
      </div>
      <button class="btn-primary" onclick="openScanner()" style="margin-bottom:12px">
        <i class="fas fa-camera"></i> Buka Kamera Scanner
      </button>
      <div class="form-group">
        <label class="form-label">Atau Masukkan Kode QR Manual</label>
        <input type="text" id="manualQR" class="form-input" placeholder="Paste kode QR disini...">
      </div>
      <button class="btn-secondary" onclick="processManualQR()">
        <i class="fas fa-arrow-right"></i> Proses QR
      </button>
    </div>

    <div style="height:20px"></div>
  </div>

  <div class="bottom-nav">
    <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="mutasi.php" class="nav-item"><i class="fas fa-receipt"></i><span>Mutasi</span></a>
    <a href="qr.php" class="nav-qr"><i class="fas fa-qrcode"></i></a>
    <a href="merchant.php" class="nav-item"><i class="fas fa-store"></i><span>Merchant</span></a>
    <a href="profile.php" class="nav-item"><i class="fas fa-user"></i><span>Profil</span></a>
  </div>
</div>
</div>

<script>
const qrData = <?= $qr_data ?>;
let qrInstance = null;
let timerSeconds = 300;
let timerInterval;

function generateQR(data) {
  document.getElementById('qrcode').innerHTML = '';
  qrInstance = new QRCode(document.getElementById('qrcode'), {
    text: JSON.stringify(data),
    width: 200, height: 200,
    colorDark: '#1a0050', colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H
  });
}

generateQR(qrData);
startTimer();

function startTimer() {
  clearInterval(timerInterval);
  timerSeconds = 300;
  timerInterval = setInterval(() => {
    timerSeconds--;
    const m = String(Math.floor(timerSeconds/60)).padStart(2,'0');
    const s = String(timerSeconds%60).padStart(2,'0');
    document.getElementById('qrTimer').textContent = m + ':' + s;
    if (timerSeconds <= 0) { clearInterval(timerInterval); document.getElementById('qrTimer').textContent = 'Kadaluarsa'; document.getElementById('qrTimer').style.color = '#EF4444'; }
  }, 1000);
}

function refreshQR() {
  qrData.ts = Date.now();
  generateQR(qrData);
  startTimer();
  Swal.fire({toast:true,position:'top',icon:'success',title:'QR diperbarui!',timer:1500,showConfirmButton:false,background:'#1A1A2E',color:'#fff'});
}

function generateAmountQR() {
  const amt = parseInt(document.getElementById('specificAmount').value);
  if (!amt || amt < 1000) return Swal.fire({icon:'warning',title:'Nominal salah',text:'Minimal Rp 1.000',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
  const data = { ...qrData, amount: amt, ts: Date.now() };
  generateQR(data);
  startTimer();
  Swal.fire({toast:true,position:'top',icon:'success',title:`QR Rp ${amt.toLocaleString('id-ID')} siap!`,timer:2000,showConfirmButton:false,background:'#1A1A2E',color:'#fff'});
}

function switchTab(tab) {
  document.querySelectorAll('.qr-tab').forEach((t,i) => t.classList.toggle('active', (tab==='show'&&i===0)||(tab==='scan'&&i===1)));
  document.getElementById('panel-show').classList.toggle('active', tab==='show');
  document.getElementById('panel-scan').classList.toggle('active', tab==='scan');
}

function openScanner() {
  window.location.href = 'scan.php';
}

async function processManualQR() {
  const code = document.getElementById('manualQR').value.trim();
  if (!code) return;
  try {
    const data = JSON.parse(code);
    window.location.href = `pembayaran.php?data=${encodeURIComponent(code)}`;
  } catch {
    Swal.fire({icon:'error',title:'QR Tidak Valid',text:'Format kode QR tidak dikenali.',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
  }
}

function shareQR() {
  Swal.fire({icon:'info',title:'Bagikan QR',text:'Fitur berbagi QR akan segera hadir!',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
}
</script>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
