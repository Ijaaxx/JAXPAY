<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
$user_id = $_SESSION['user_id'];
$user    = $koneksi->query("SELECT saldo FROM users WHERE id=$user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Scan QR</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<link rel="stylesheet" href="../assets/css/mobile.css?v=5">
<style>
.scanner-area {
  margin: 16px;
  background: #000;
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  min-height: 300px;
}
#reader { width: 100%; }
#reader video { border-radius: 0 !important; }

.scan-overlay {
  position: absolute; inset: 0; pointer-events: none;
  display: flex; align-items: center; justify-content: center;
}
.scan-frame {
  width: 200px; height: 200px; position: relative;
}
.scan-corner {
  position: absolute; width: 28px; height: 28px;
  border-color: var(--primary-light); border-style: solid;
}
.scan-corner.tl { top:0; left:0; border-width:3px 0 0 3px; border-radius:6px 0 0 0; }
.scan-corner.tr { top:0; right:0; border-width:3px 3px 0 0; border-radius:0 6px 0 0; }
.scan-corner.bl { bottom:0; left:0; border-width:0 0 3px 3px; border-radius:0 0 0 6px; }
.scan-corner.br { bottom:0; right:0; border-width:0 3px 3px 0; border-radius:0 0 6px 0; }
.scan-line {
  position: absolute; left:12px; right:12px; height:2px;
  background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
  animation: scanAnim 2s ease-in-out infinite;
}
@keyframes scanAnim {
  0%,100% { top: 12px; opacity:0; }
  10% { opacity:1; }
  90% { opacity:1; }
  50% { top: calc(100% - 14px); }
}

.manual-section { padding: 0 16px; margin-top: 4px; }
.scan-status {
  margin: 12px 16px 0; padding: 12px 16px;
  background: rgba(108,60,225,0.1); border: 1px solid rgba(108,60,225,0.3);
  border-radius: 14px; font-size: 13px; text-align: center; color: var(--text-muted);
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.scan-status.success { background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.3); color: #10B981; }
.scan-status.error { background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); color: #EF4444; }
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
      <div>
        <div class="page-title">Scan QR Merchant</div>
        <div style="font-size:12px;color:var(--text-muted)">Saldo: Rp <?= number_format($user['saldo'],0,',','.') ?></div>
      </div>
    </div>

    <!-- Scanner -->
    <div class="scanner-area animate-up-1">
      <div id="reader"></div>
      <div class="scan-overlay">
        <div class="scan-frame">
          <div class="scan-corner tl"></div>
          <div class="scan-corner tr"></div>
          <div class="scan-corner bl"></div>
          <div class="scan-corner br"></div>
          <div class="scan-line"></div>
        </div>
      </div>
    </div>

    <div class="scan-status animate-up-2" id="scanStatus">
      <i class="fas fa-camera fa-pulse"></i>
      Arahkan kamera ke QR Code merchant...
    </div>

    <!-- Manual Input -->
    <div class="manual-section animate-up-3" style="margin-top:16px">
      <div style="text-align:center;color:var(--text-muted);font-size:12px;margin-bottom:12px">— atau input manual —</div>
      <div class="form-group">
        <label class="form-label">Kode Member ID Penerima</label>
        <input type="text" id="manualMemberId" class="form-input" placeholder="Contoh: JAX-MCH001">
      </div>
      <div class="form-group">
        <label class="form-label">Nominal (Rp)</label>
        <input type="number" id="manualAmount" class="form-input" placeholder="Masukkan nominal..." min="1000">
      </div>
      <button class="btn-primary" onclick="processManual()" style="margin-top:4px">
        <i class="fas fa-bolt"></i> Proses Pembayaran Manual
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
const userSaldo = <?= (float)$user['saldo'] ?>;
let scannerStarted = false;
let html5QrCode;

function startScanner() {
  html5QrCode = new Html5Qrcode("reader");
  html5QrCode.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 200, height: 200 } },
    (decodedText) => {
      if (scannerStarted) return;
      scannerStarted = true;
      html5QrCode.stop();
      handleQRResult(decodedText);
    },
    (err) => {}
  ).catch(err => {
    document.getElementById('scanStatus').innerHTML = '<i class="fas fa-exclamation-triangle"></i> Kamera tidak dapat diakses. Gunakan input manual.';
    document.getElementById('scanStatus').className = 'scan-status error';
  });
}

function handleQRResult(raw) {
  const statusEl = document.getElementById('scanStatus');
  try {
    const data = JSON.parse(raw);
    if (data.type === 'jaxpay_user' || data.type === 'jaxpay_merchant') {
      statusEl.innerHTML = '<i class="fas fa-check-circle"></i> QR Terdeteksi: ' + (data.nama || data.member_id);
      statusEl.className = 'scan-status success';
      showPaymentPrompt(data);
    } else {
      statusEl.innerHTML = '<i class="fas fa-times-circle"></i> QR tidak dikenali. Coba lagi.';
      statusEl.className = 'scan-status error';
      setTimeout(() => { scannerStarted = false; startScanner(); }, 2500);
    }
  } catch(e) {
    statusEl.innerHTML = '<i class="fas fa-times-circle"></i> Format QR tidak valid.';
    statusEl.className = 'scan-status error';
    setTimeout(() => { scannerStarted = false; startScanner(); }, 2500);
  }
}

async function showPaymentPrompt(qrData) {
  const { value } = await Swal.fire({
    title: '💳 Bayar ke ' + (qrData.nama || 'Pengguna'),
    html: `<div style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:8px">Saldo Anda: <strong>Rp ${userSaldo.toLocaleString('id-ID')}</strong></div>
           ${qrData.amount ? `<div style="font-size:16px;font-weight:700;color:#10B981;margin-bottom:12px">Nominal: Rp ${parseInt(qrData.amount).toLocaleString('id-ID')}</div>` : ''}
           <input id="payAmount" type="number" class="swal2-input" placeholder="Nominal pembayaran (Rp)..." min="1000" value="${qrData.amount||''}" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;border-radius:12px">
           <input id="payNote" type="text" class="swal2-input" placeholder="Keterangan (opsional)..." style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;border-radius:12px;margin-top:8px">`,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-bolt"></i> Bayar Sekarang',
    cancelButtonText: 'Batal',
    background: '#1A1A2E', color: '#fff',
    confirmButtonColor: '#6C3CE1', cancelButtonColor: '#374151',
    preConfirm: () => {
      const amt = parseInt(document.getElementById('payAmount').value);
      const note = document.getElementById('payNote').value;
      if (!amt || amt < 1000) { Swal.showValidationMessage('Minimal Rp 1.000'); return false; }
      if (amt > userSaldo) { Swal.showValidationMessage('Saldo tidak cukup!'); return false; }
      return { amount: amt, note, qrData };
    }
  });

  if (!value) { scannerStarted = false; startScanner(); return; }

  Swal.fire({ title:'Memproses...', allowOutsideClick:false, didOpen:()=>Swal.showLoading(), background:'#1A1A2E', color:'#fff' });

  // Find merchant by member_id or user_id
  const resp = await fetch('../proses/qr_payment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `qr_data=${encodeURIComponent(JSON.stringify(value.qrData))}&jumlah=${value.amount}&catatan=${encodeURIComponent(value.note)}`
  });
  const result = await resp.json();

  if (result.success) {
    Swal.fire({
      icon: 'success', title: 'Pembayaran Berhasil! 🎉',
      html: `<strong>Rp ${value.amount.toLocaleString('id-ID')}</strong> berhasil dibayar!<br><small>Kode: ${result.kode}</small>`,
      background:'#1A1A2E', color:'#fff', confirmButtonColor:'#6C3CE1', timer: 3000
    }).then(() => window.location.href = 'home.php');
  } else {
    Swal.fire({ icon:'error', title:'Gagal', text: result.message, background:'#1A1A2E', color:'#fff', confirmButtonColor:'#6C3CE1' });
    scannerStarted = false;
    startScanner();
  }
}

async function processManual() {
  const memberId = document.getElementById('manualMemberId').value.trim();
  const amount   = parseInt(document.getElementById('manualAmount').value);

  if (!memberId) return Swal.fire({icon:'warning',title:'Member ID Wajib',text:'Masukkan Member ID tujuan.',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
  if (!amount || amount < 1000) return Swal.fire({icon:'warning',title:'Nominal Salah',text:'Minimal Rp 1.000',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
  if (amount > userSaldo) return Swal.fire({icon:'error',title:'Saldo Tidak Cukup',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});

  Swal.fire({title:'Memproses...',allowOutsideClick:false,didOpen:()=>Swal.showLoading(),background:'#1A1A2E',color:'#fff'});
  const resp = await fetch('../proses/qr_payment.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: `member_id=${encodeURIComponent(memberId)}&jumlah=${amount}&catatan=`
  });
  const result = await resp.json();
  if (result.success) {
    Swal.fire({icon:'success',title:'Pembayaran Berhasil!',html:`Rp ${amount.toLocaleString('id-ID')} ke <strong>${result.nama}</strong>`,background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1',timer:3000}).then(()=>window.location.href='home.php');
  } else {
    Swal.fire({icon:'error',title:'Gagal',text:result.message,background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
  }
}

// Auto start scanner
window.addEventListener('load', startScanner);
</script>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
