<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
$user_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tx = $koneksi->query("SELECT * FROM transaksi WHERE id=$id AND user_id=$user_id")->fetch_assoc();
if (!$tx) { header('Location: mutasi.php'); exit; }

$is_positive = in_array($tx['tipe'], ['topup','transfer_masuk']);
$tipe_labels = ['topup'=>'Top Up Saldo','transfer_masuk'=>'Terima Transfer','transfer_keluar'=>'Kirim Transfer','pembayaran'=>'Pembayaran Merchant','qr_payment'=>'QR Payment'];
$tipe_icon_map = ['topup'=>['topup','fa-arrow-down-to-line'],'transfer_masuk'=>['in','fa-arrow-down'],'transfer_keluar'=>['out','fa-arrow-up'],'pembayaran'=>['pay','fa-store'],'qr_payment'=>['qr','fa-qrcode']];
$ic = $tipe_icon_map[$tx['tipe']] ?? ['pay','fa-receipt'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Detail Transaksi</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/mobile.css">
<style>
.detail-hero {
  text-align: center; padding: 30px 20px 24px;
}
.detail-amount-icon { width: 72px; height: 72px; border-radius: 24px; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 16px; }
.detail-amount-icon.topup { background: rgba(16,185,129,0.2); color: #10B981; }
.detail-amount-icon.in { background: rgba(0,212,255,0.2); color: #00D4FF; }
.detail-amount-icon.out { background: rgba(239,68,68,0.2); color: #EF4444; }
.detail-amount-icon.pay { background: rgba(108,60,225,0.2); color: #9B72EF; }
.detail-amount-icon.qr { background: rgba(251,191,36,0.2); color: #FBBF24; }
.detail-hero h2 { font-size: 28px; font-weight: 800; margin-bottom: 6px; }
.detail-hero h2.pos { color: #10B981; }
.detail-hero h2.neg { color: #EF4444; }
.detail-type { font-size: 14px; color: var(--text-muted); }

.detail-card {
  margin: 0 16px 12px; background: var(--card); border: 1px solid var(--border); border-radius: 20px; overflow: hidden;
}
.detail-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 14px 18px; border-bottom: 1px solid var(--border);
}
.detail-row:last-child { border-bottom: none; }
.detail-row .d-label { font-size: 12px; color: var(--text-muted); }
.detail-row .d-val { font-size: 13px; font-weight: 600; text-align: right; max-width: 60%; }
.kode-trx { font-family: monospace; font-size: 12px; color: var(--accent); }
.status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
.status-success { background: rgba(16,185,129,0.15); color: #10B981; }
.status-pending { background: rgba(245,158,11,0.15); color: #F59E0B; }
.status-failed { background: rgba(239,68,68,0.15); color: #EF4444; }
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
      <a href="mutasi.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
      <div class="page-title">Detail Transaksi</div>
    </div>

    <div class="detail-hero animate-up-1">
      <div class="detail-amount-icon <?= $ic[0] ?>"><i class="fas <?= $ic[1] ?>"></i></div>
      <h2 class="<?= $is_positive?'pos':'neg' ?>">
        <?= $is_positive?'+':'-' ?>Rp <?= number_format($tx['jumlah'],0,',','.') ?>
      </h2>
      <div class="detail-type"><?= $tipe_labels[$tx['tipe']] ?? $tx['tipe'] ?></div>
      <div style="margin-top:8px">
        <span class="status-badge status-<?= $tx['status'] ?>"><?= ucfirst($tx['status']) ?></span>
      </div>
    </div>

    <div class="detail-card animate-up-2">
      <div class="detail-row">
        <div class="d-label">Kode Transaksi</div>
        <div class="d-val kode-trx"><?= $tx['kode_transaksi'] ?></div>
      </div>
      <div class="detail-row">
        <div class="d-label">Tanggal & Waktu</div>
        <div class="d-val"><?= date('d M Y, H:i', strtotime($tx['created_at'])) ?></div>
      </div>
      <div class="detail-row">
        <div class="d-label">Keterangan</div>
        <div class="d-val"><?= htmlspecialchars($tx['keterangan']?:'-') ?></div>
      </div>
      <div class="detail-row">
        <div class="d-label">Tipe Transaksi</div>
        <div class="d-val"><?= $tipe_labels[$tx['tipe']] ?? $tx['tipe'] ?></div>
      </div>
    </div>

    <div class="detail-card animate-up-3">
      <div class="detail-row">
        <div class="d-label">Saldo Sebelum</div>
        <div class="d-val">Rp <?= number_format($tx['saldo_sebelum'],0,',','.') ?></div>
      </div>
      <div class="detail-row">
        <div class="d-label">Jumlah</div>
        <div class="d-val" style="color:<?= $is_positive?'#10B981':'#EF4444' ?>">
          <?= $is_positive?'+':'-' ?>Rp <?= number_format($tx['jumlah'],0,',','.') ?>
        </div>
      </div>
      <div class="detail-row">
        <div class="d-label">Saldo Setelah</div>
        <div class="d-val" style="color:var(--accent)">Rp <?= number_format($tx['saldo_sesudah'],0,',','.') ?></div>
      </div>
    </div>

    <div style="padding:0 16px 20px" class="animate-up-4">
      <a href="mutasi.php" class="btn-secondary" style="display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;margin-top:8px">
        <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
      </a>
    </div>
  </div>

  <div class="bottom-nav">
    <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="mutasi.php" class="nav-item active"><i class="fas fa-receipt"></i><span>Mutasi</span></a>
    <a href="qr.php" class="nav-qr"><i class="fas fa-qrcode"></i></a>
    <a href="merchant.php" class="nav-item"><i class="fas fa-store"></i><span>Merchant</span></a>
    <a href="profile.php" class="nav-item"><i class="fas fa-user"></i><span>Profil</span></a>
  </div>
</div>
</div>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
