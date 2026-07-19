<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
$user_id = $_SESSION['user_id'];
$user = $koneksi->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Top Up</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/mobile.css">
<style>
.section-pad { padding: 0 16px; }
.bank-list { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
.bank-card {
  background: var(--card); border: 2px solid var(--border);
  border-radius: 16px; padding: 14px 12px; cursor: pointer;
  transition: all 0.2s; text-align: center;
}
.bank-card:hover, .bank-card.selected {
  border-color: var(--primary-light);
  background: rgba(108,60,225,0.12);
}
.bank-logo { font-size: 22px; margin-bottom: 6px; }
.bank-name { font-size: 13px; font-weight: 700; color: #fff; }
.bank-acc { font-size: 11px; color: var(--text-muted); margin-top: 3px; }

.amount-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 12px 0; }
.preset-btn {
  background: rgba(255,255,255,0.06); border: 1px solid var(--border);
  border-radius: 12px; padding: 12px; text-align: center;
  cursor: pointer; transition: all 0.2s; font-family: inherit;
  color: rgba(255,255,255,0.8); font-size: 13px; font-weight: 600;
}
.preset-btn:hover, .preset-btn.active {
  border-color: var(--primary-light); color: var(--primary-light);
  background: rgba(108,60,225,0.12);
}

.upload-area {
  background: rgba(255,255,255,0.04); border: 2px dashed var(--border);
  border-radius: 16px; padding: 24px; text-align: center;
  cursor: pointer; transition: all 0.3s; position: relative;
}
.upload-area:hover, .upload-area.dragover {
  border-color: var(--primary-light);
  background: rgba(108,60,225,0.08);
}
.upload-area input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.upload-icon { font-size: 32px; color: var(--primary-light); margin-bottom: 10px; }
.upload-text { font-size: 14px; font-weight: 600; color: rgba(255,255,255,0.7); }
.upload-sub { font-size: 11px; color: var(--text-muted); margin-top: 4px; }
.preview-img { max-width: 100%; border-radius: 12px; margin-top: 10px; display: none; }

.info-card {
  background: rgba(0,212,255,0.08); border: 1px solid rgba(0,212,255,0.2);
  border-radius: 14px; padding: 14px; margin-bottom: 16px;
}
.info-card h4 { font-size: 13px; font-weight: 700; color: var(--accent); margin-bottom: 8px; }
.info-card p { font-size: 12px; color: rgba(255,255,255,0.65); line-height: 1.6; }
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
        <div class="page-title">Top Up Saldo</div>
        <div style="font-size:12px;color:var(--text-muted)">Saldo: Rp <?= number_format($user['saldo'],0,',','.') ?></div>
      </div>
    </div>

    <div class="section-pad" style="margin-top:16px">

      <!-- Info -->
      <div class="info-card animate-up-1">
        <h4><i class="fas fa-info-circle"></i> Cara Top Up</h4>
        <p>1. Pilih bank tujuan transfer<br>
           2. Masukkan nominal yang diinginkan<br>
           3. Transfer ke rekening di bawah<br>
           4. Upload bukti transfer<br>
           5. Tunggu konfirmasi admin (maks. 1x24 jam)</p>
      </div>

      <!-- Bank Selection -->
      <div class="section-title" style="padding:0 0 12px">Pilih Bank Tujuan</div>
      <div class="bank-list animate-up-1">
        <div class="bank-card selected" data-bank="BCA" data-no="1234567890" onclick="selectBank(this)">
          <div class="bank-logo">🏦</div>
          <div class="bank-name">BCA</div>
          <div class="bank-acc">1234-5678-90</div>
        </div>
        <div class="bank-card" data-bank="Mandiri" data-no="0987654321" onclick="selectBank(this)">
          <div class="bank-logo">🏛️</div>
          <div class="bank-name">Mandiri</div>
          <div class="bank-acc">0987-6543-21</div>
        </div>
        <div class="bank-card" data-bank="BNI" data-no="1122334455" onclick="selectBank(this)">
          <div class="bank-logo">🏗️</div>
          <div class="bank-name">BNI</div>
          <div class="bank-acc">1122-3344-55</div>
        </div>
        <div class="bank-card" data-bank="BRI" data-no="5544332211" onclick="selectBank(this)">
          <div class="bank-logo">🌾</div>
          <div class="bank-name">BRI</div>
          <div class="bank-acc">5544-3322-11</div>
        </div>
      </div>

      <!-- Transfer Target -->
      <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);border-radius:14px;padding:14px;margin-bottom:16px;" class="animate-up-2">
        <div style="font-size:11px;color:#10B981;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Transfer ke a/n JAXPAY SCHOOL</div>
        <div style="font-size:22px;font-weight:800;color:#fff;letter-spacing:2px;" id="bankNoDisplay">1234-5678-90</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;" id="bankNameDisplay">BCA</div>
        <button onclick="copyRekening()" style="margin-top:10px;background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.3);color:#10B981;border-radius:10px;padding:6px 14px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;">
          <i class="fas fa-copy"></i> Salin Nomor
        </button>
      </div>

      <!-- Amount -->
      <div class="section-title" style="padding:0 0 12px">Nominal Top Up</div>
      <div class="amount-grid animate-up-2">
        <button class="preset-btn" onclick="setNominal(10000)">Rp 10.000</button>
        <button class="preset-btn" onclick="setNominal(20000)">Rp 20.000</button>
        <button class="preset-btn" onclick="setNominal(50000)">Rp 50.000</button>
        <button class="preset-btn" onclick="setNominal(100000)">Rp 100.000</button>
        <button class="preset-btn" onclick="setNominal(200000)">Rp 200.000</button>
        <button class="preset-btn" onclick="setNominal(500000)">Rp 500.000</button>
      </div>
      <div style="position:relative;margin-bottom:16px;" class="animate-up-3">
        <span style="position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:700;">Rp</span>
        <input type="number" id="nominalInput" class="form-input" style="padding-left:46px;font-size:18px;font-weight:700;" placeholder="Nominal lainnya..." min="10000">
      </div>

      <!-- Upload Bukti -->
      <div class="section-title" style="padding:0 0 12px">Upload Bukti Transfer</div>
      <div class="upload-area animate-up-3" id="uploadArea">
        <input type="file" id="buktiFile" accept="image/*" onchange="previewBukti(this)">
        <div id="uploadPlaceholder">
          <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
          <div class="upload-text">Ketuk untuk upload bukti</div>
          <div class="upload-sub">JPG, PNG, max 2MB</div>
        </div>
        <img id="previewImg" class="preview-img">
      </div>

      <!-- Catatan -->
      <div class="form-group" style="margin-top:16px" class="animate-up-4">
        <label class="form-label">Catatan (Opsional)</label>
        <input type="text" id="catatanInput" class="form-input" placeholder="Contoh: Top up untuk jajan">
      </div>

      <button class="btn-primary ripple-btn animate-up-4" onclick="submitTopup()" id="btnTopup" style="margin-top:8px">
        <i class="fas fa-upload"></i> Ajukan Top Up
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
let selectedBank = 'BCA', selectedBankNo = '1234567890';

function selectBank(el) {
  document.querySelectorAll('.bank-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  selectedBank = el.dataset.bank;
  selectedBankNo = el.dataset.no;
  document.getElementById('bankNameDisplay').textContent = selectedBank;
  document.getElementById('bankNoDisplay').textContent = selectedBankNo.replace(/(.{4})/g,'$1-').slice(0,-1);
}

function setNominal(val) {
  document.getElementById('nominalInput').value = val;
  document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
}

function copyRekening() {
  navigator.clipboard.writeText(selectedBankNo);
  Swal.fire({toast:true,position:'top',icon:'success',title:'Nomor rekening disalin!',timer:1500,showConfirmButton:false,background:'#1A1A2E',color:'#fff'});
}

function previewBukti(input) {
  const file = input.files[0];
  if (!file) return;
  if (file.size > 2*1024*1024) {
    Swal.fire({icon:'error',title:'File terlalu besar',text:'Maksimal 2MB',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
    return;
  }
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('uploadPlaceholder').style.display = 'none';
    const img = document.getElementById('previewImg');
    img.src = e.target.result;
    img.style.display = 'block';
  };
  reader.readAsDataURL(file);
}

async function submitTopup() {
  const nominal = parseInt(document.getElementById('nominalInput').value);
  const bukti = document.getElementById('buktiFile').files[0];
  const catatan = document.getElementById('catatanInput').value;

  if (!nominal || nominal < 10000)
    return Swal.fire({icon:'warning',title:'Nominal Kurang',text:'Minimal top up Rp 10.000',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
  if (!bukti)
    return Swal.fire({icon:'warning',title:'Upload Bukti',text:'Upload bukti transfer terlebih dahulu.',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});

  const btn = document.getElementById('btnTopup');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Mengirim...';

  const fd = new FormData();
  fd.append('nominal', nominal);
  fd.append('bank', selectedBank);
  fd.append('bukti', bukti);
  fd.append('catatan', catatan);

  Swal.fire({title:'Mengirim pengajuan...',allowOutsideClick:false,didOpen:()=>Swal.showLoading(),background:'#1A1A2E',color:'#fff'});

  const resp = await fetch('../proses/topup.php', {method:'POST', body:fd});
  const data = await resp.json();

  if (data.success) {
    Swal.fire({
      icon:'success', title:'Top Up Diajukan! 🎉',
      html:`Pengajuan top up <strong>Rp ${nominal.toLocaleString('id-ID')}</strong> berhasil dikirim.<br><small>Kode: ${data.kode}</small><br><br><small style="color:var(--text-muted)">Tunggu konfirmasi admin maks. 1x24 jam</small>`,
      background:'#1A1A2E', color:'#fff', confirmButtonColor:'#6C3CE1'
    }).then(() => window.location.href = 'home.php');
  } else {
    Swal.fire({icon:'error',title:'Gagal',text:data.message||'Terjadi kesalahan.',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-upload"></i> Ajukan Top Up';
  }
}
</script>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
