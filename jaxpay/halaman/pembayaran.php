<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }

$user_id  = (int)$_SESSION['user_id'];
$user     = $koneksi->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$qr_raw   = urldecode($_GET['data'] ?? '');
$merchant_id = (int)($_GET['merchant_id'] ?? 0);

$target = null;
$is_merchant = false;

if ($qr_raw) {
    try {
        $qr_data = json_decode($qr_raw, true);
        $uid = (int)($qr_data['user_id'] ?? 0);
        if ($uid) $target = $koneksi->query("SELECT id,nama,role,member_id FROM users WHERE id=$uid")->fetch_assoc();
        if ($target && $target['role'] === 'merchant') {
            $m = $koneksi->query("SELECT * FROM merchant WHERE user_id={$target['id']}")->fetch_assoc();
            $is_merchant = !!$m;
        }
    } catch (Exception $e) {}
}

if ($merchant_id) {
    $m = $koneksi->query("SELECT m.*,u.nama,u.id as uid FROM merchant m JOIN users u ON m.user_id=u.id WHERE m.id=$merchant_id AND m.is_active=1")->fetch_assoc();
    if ($m) { $target = $m; $is_merchant = true; }
}

if (!$target) {
    header('Location: scan.php');
    exit;
}

$preset_amount = isset($qr_data['amount']) ? (int)$qr_data['amount'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Pembayaran</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/mobile.css?v=5">
<style>
.payment-hero { padding:24px 20px; text-align:center; }
.payment-icon { width:80px;height:80px;border-radius:24px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:36px;color:#fff;margin:0 auto 16px;box-shadow:0 8px 32px rgba(108,60,225,0.4); }
.payment-card { margin:0 16px 16px; background:var(--card); border:1px solid var(--border); border-radius:20px; overflow:hidden; }
.payment-row { display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:1px solid var(--border); }
.payment-row:last-child { border-bottom:none; }
.p-label { font-size:12px;color:var(--text-muted); }
.p-val { font-size:14px;font-weight:600; }
.amount-display { font-size:36px;font-weight:800;color:var(--primary-light);margin-bottom:8px; }
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
      <a href="scan.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
      <div class="page-title">Konfirmasi Pembayaran</div>
    </div>

    <div class="payment-hero animate-up-1">
      <div class="payment-icon">
        <?= $is_merchant ? '🏪' : '<i class="fas fa-user"></i>' ?>
      </div>
      <h2 style="font-size:20px;font-weight:800;margin-bottom:4px"><?= htmlspecialchars($target['nama'] ?? $target['nama_toko'] ?? '') ?></h2>
      <p style="color:var(--text-muted);font-size:13px"><?= $is_merchant ? 'Merchant JAXPAY' : 'Pengguna JAXPAY' ?></p>
    </div>

    <!-- Payer info -->
    <div class="payment-card animate-up-2">
      <div class="payment-row">
        <div class="p-label">Saldo Anda</div>
        <div class="p-val" style="color:#10B981">Rp <?= number_format($user['saldo'],0,',','.') ?></div>
      </div>
      <div class="payment-row">
        <div class="p-label">Tujuan Pembayaran</div>
        <div class="p-val"><?= htmlspecialchars($target['nama'] ?? $target['nama_toko'] ?? '') ?></div>
      </div>
      <?php if (isset($target['member_id'])): ?>
      <div class="payment-row">
        <div class="p-label">Member ID</div>
        <div class="p-val" style="font-family:monospace;font-size:13px;color:var(--accent)"><?= $target['member_id'] ?></div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Amount input -->
    <div style="padding:0 16px" class="animate-up-3">
      <div class="section-title" style="padding:0 0 12px;font-size:14px;font-weight:700">Nominal Pembayaran</div>
      <div style="position:relative;margin-bottom:12px">
        <span style="position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:700;font-size:16px">Rp</span>
        <input type="number" id="amountInput" class="form-input" style="padding-left:44px;font-size:22px;font-weight:700;"
          placeholder="0" min="1000" max="<?= $user['saldo'] ?>"
          value="<?= $preset_amount ?: '' ?>" <?= $preset_amount ? 'readonly' : '' ?>>
      </div>
      <?php if (!$preset_amount): ?>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
        <?php foreach ([5000,10000,15000,20000,25000,50000] as $amt): ?>
        <button onclick="document.getElementById('amountInput').value=<?=$amt?>" style="padding:7px 12px;border-radius:10px;border:1px solid var(--border);background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.7);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:all 0.2s" onmouseover="this.style.borderColor='var(--primary-light)'" onmouseout="this.style.borderColor='var(--border)'">
          <?= number_format($amt,0,',','.') ?>
        </button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="form-group">
        <label class="form-label">Keterangan (Opsional)</label>
        <input type="text" id="noteInput" class="form-input" placeholder="Tulis keterangan...">
      </div>
      <button class="btn-primary ripple-btn" onclick="submitPayment()" id="btnPay" style="margin-top:4px">
        <i class="fas fa-bolt"></i> Bayar Sekarang
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
const userSaldo   = <?= (float)$user['saldo'] ?>;
const merchantId  = <?= $is_merchant && isset($target['id']) ? (isset($target['user_id'])?$target['id']:0) : 0 ?>;
const targetName  = '<?= htmlspecialchars(addslashes($target['nama'] ?? $target['nama_toko'] ?? '')) ?>';
const qrData      = <?= $qr_raw ? json_encode($qr_raw) : 'null' ?>;
const memberId    = '<?= htmlspecialchars($target['member_id'] ?? '') ?>';

async function submitPayment() {
  const amount = parseInt(document.getElementById('amountInput').value);
  const note   = document.getElementById('noteInput').value;

  if (!amount || amount < 1000) return Swal.fire({icon:'warning',title:'Nominal Salah',text:'Minimal pembayaran Rp 1.000',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
  if (amount > userSaldo) return Swal.fire({icon:'error',title:'Saldo Tidak Cukup',text:'Saldo Anda tidak mencukupi.',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});

  const confirm = await Swal.fire({
    title:'Konfirmasi Pembayaran',
    html:`Bayar <strong>Rp ${amount.toLocaleString('id-ID')}</strong> ke <strong>${targetName}</strong>?`,
    icon:'question', showCancelButton:true,
    confirmButtonText:'<i class="fas fa-bolt"></i> Bayar',
    cancelButtonText:'Batal',
    background:'#1A1A2E', color:'#fff', confirmButtonColor:'#6C3CE1', cancelButtonColor:'#374151'
  });
  if (!confirm.isConfirmed) return;

  document.getElementById('btnPay').disabled = true;
  document.getElementById('btnPay').innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Memproses...';
  Swal.fire({title:'Memproses...',allowOutsideClick:false,didOpen:()=>Swal.showLoading(),background:'#1A1A2E',color:'#fff'});

  const body = qrData
    ? `qr_data=${encodeURIComponent(qrData)}&jumlah=${amount}&catatan=${encodeURIComponent(note)}`
    : `member_id=${encodeURIComponent(memberId)}&jumlah=${amount}&catatan=${encodeURIComponent(note)}`;

  const resp = await fetch('../proses/qr_payment.php', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body});
  const data = await resp.json();

  if (data.success) {
    Swal.fire({icon:'success',title:'Pembayaran Berhasil! 🎉',
      html:`<strong>Rp ${amount.toLocaleString('id-ID')}</strong> ke <strong>${targetName}</strong><br><small>Kode: ${data.kode}</small>`,
      background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1',timer:3000
    }).then(()=>window.location.href='home.php');
  } else {
    Swal.fire({icon:'error',title:'Gagal',text:data.message,background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
    document.getElementById('btnPay').disabled=false;
    document.getElementById('btnPay').innerHTML='<i class="fas fa-bolt"></i> Bayar Sekarang';
  }
}
</script>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
