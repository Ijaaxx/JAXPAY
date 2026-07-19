<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
$user_id = $_SESSION['user_id'];
$user = $koneksi->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

$filter = isset($_GET['filter']) ? sanitize($_GET['filter']) : 'all';
$where = "WHERE user_id=$user_id";
if ($filter !== 'all') {
  $map = ['topup'=>"tipe='topup'",'masuk'=>"tipe='transfer_masuk'",'keluar'=>"tipe='transfer_keluar'",'bayar'=>"tipe IN ('pembayaran','qr_payment')"];
  if (isset($map[$filter])) $where .= " AND " . $map[$filter];
}

$transaksi = $koneksi->query("SELECT * FROM transaksi $where ORDER BY created_at DESC LIMIT 50");

// Summary
$sum = $koneksi->query("SELECT
  SUM(CASE WHEN tipe IN ('topup','transfer_masuk') THEN jumlah ELSE 0 END) as total_in,
  SUM(CASE WHEN tipe IN ('transfer_keluar','pembayaran','qr_payment') THEN jumlah ELSE 0 END) as total_out,
  COUNT(*) as total_tx
  FROM transaksi WHERE user_id=$user_id")->fetch_assoc();

$tipe_labels = ['topup'=>'Top Up','transfer_masuk'=>'Terima Transfer','transfer_keluar'=>'Kirim Transfer','pembayaran'=>'Pembayaran Merchant','qr_payment'=>'QR Payment'];
$tipe_icon = ['topup'=>['topup','fa-arrow-down-to-line'],'transfer_masuk'=>['in','fa-arrow-down'],'transfer_keluar'=>['out','fa-arrow-up'],'pembayaran'=>['pay','fa-store'],'qr_payment'=>['qr','fa-qrcode']];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Riwayat Transaksi</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/mobile.css">
<style>
.filter-tabs {
  display: flex; gap: 8px; padding: 12px 16px; overflow-x: auto; scrollbar-width: none;
}
.filter-tabs::-webkit-scrollbar { display: none; }
.filter-chip {
  flex-shrink: 0; padding: 8px 16px; border-radius: 20px;
  border: 1px solid var(--border); background: transparent;
  color: var(--text-muted); font-size: 12px; font-weight: 600;
  cursor: pointer; transition: all 0.2s; font-family: inherit;
  white-space: nowrap;
}
.filter-chip.active { background: var(--primary); color: #fff; border-color: var(--primary); }

.summary-row {
  display: flex; gap: 10px; padding: 0 16px 12px;
}
.summary-box {
  flex: 1; background: var(--card); border: 1px solid var(--border);
  border-radius: 14px; padding: 12px; text-align: center;
}
.summary-box .s-label { font-size: 10px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.summary-box .s-val { font-size: 13px; font-weight: 700; }
.s-val.in { color: #10B981; }
.s-val.out { color: #EF4444; }
.s-val.total { color: var(--accent); }

.tx-group-header {
  padding: 10px 16px 6px;
  font-size: 12px; font-weight: 700; color: var(--text-muted);
  text-transform: uppercase; letter-spacing: 0.8px;
}
.tx-list { padding: 0 16px; }
.tx-item {
  display: flex; align-items: center; gap: 12px;
  padding: 13px 12px; background: var(--card); border: 1px solid var(--border);
  border-radius: 14px; margin-bottom: 8px; cursor: pointer; transition: all 0.2s;
  text-decoration: none;
}
.tx-item:hover { background: var(--card-hover); border-color: rgba(108,60,225,0.3); transform: translateX(3px); }
.tx-icon { width: 44px; height: 44px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.tx-icon.topup { background: rgba(16,185,129,0.15); color: #10B981; }
.tx-icon.in { background: rgba(0,212,255,0.15); color: #00D4FF; }
.tx-icon.out { background: rgba(239,68,68,0.15); color: #EF4444; }
.tx-icon.pay { background: rgba(108,60,225,0.15); color: #9B72EF; }
.tx-icon.qr { background: rgba(251,191,36,0.15); color: #FBBF24; }
.tx-info { flex: 1; min-width: 0; }
.tx-info h4 { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tx-info p { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.tx-right { text-align: right; flex-shrink: 0; }
.tx-amount { font-size: 13px; font-weight: 700; }
.tx-amount.pos { color: #10B981; }
.tx-amount.neg { color: #EF4444; }
.tx-time { font-size: 10px; color: var(--text-muted); margin-top: 3px; }

.load-more {
  display: block; width: calc(100% - 32px); margin: 8px 16px 16px;
  background: transparent; border: 1px solid var(--border);
  border-radius: 12px; padding: 12px; color: var(--text-muted);
  font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit;
  text-align: center; transition: all 0.2s;
}
.load-more:hover { border-color: var(--primary-light); color: var(--primary-light); }
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
      <div class="page-title">Riwayat Transaksi</div>
    </div>

    <!-- Summary -->
    <div class="summary-row animate-up-1" style="margin-top:14px">
      <div class="summary-box">
        <div class="s-label">Total Masuk</div>
        <div class="s-val in">+<?= number_format($sum['total_in']??0,0,',','.') ?></div>
      </div>
      <div class="summary-box">
        <div class="s-label">Total Keluar</div>
        <div class="s-val out">-<?= number_format($sum['total_out']??0,0,',','.') ?></div>
      </div>
      <div class="summary-box">
        <div class="s-label">Transaksi</div>
        <div class="s-val total"><?= $sum['total_tx']??0 ?>x</div>
      </div>
    </div>

    <!-- Filter Chips -->
    <div class="filter-tabs animate-up-2">
      <button class="filter-chip <?= $filter==='all'?'active':'' ?>" onclick="setFilter('all')">Semua</button>
      <button class="filter-chip <?= $filter==='topup'?'active':'' ?>" onclick="setFilter('topup')">Top Up</button>
      <button class="filter-chip <?= $filter==='masuk'?'active':'' ?>" onclick="setFilter('masuk')">Masuk</button>
      <button class="filter-chip <?= $filter==='keluar'?'active':'' ?>" onclick="setFilter('keluar')">Keluar</button>
      <button class="filter-chip <?= $filter==='bayar'?'active':'' ?>" onclick="setFilter('bayar')">Pembayaran</button>
    </div>

    <!-- Transaction List -->
    <div class="tx-list animate-up-3">
      <?php
      $lastDate = '';
      $count = 0;
      while ($tx = $transaksi->fetch_assoc()):
        $count++;
        $txDate = date('d F Y', strtotime($tx['created_at']));
        $isToday = $txDate === date('d F Y');
        $isYesterday = $txDate === date('d F Y', strtotime('-1 day'));
        $displayDate = $isToday ? 'Hari Ini' : ($isYesterday ? 'Kemarin' : $txDate);
        $is_positive = in_array($tx['tipe'], ['topup','transfer_masuk']);
        $ic = $tipe_icon[$tx['tipe']] ?? ['pay','fa-receipt'];
      ?>
      <?php if ($txDate !== $lastDate): $lastDate = $txDate; ?>
      <div class="tx-group-header"><?= $displayDate ?></div>
      <?php endif; ?>
      <a class="tx-item" href="detail_transaksi.php?id=<?= $tx['id'] ?>">
        <div class="tx-icon <?= $ic[0] ?>"><i class="fas <?= $ic[1] ?>"></i></div>
        <div class="tx-info">
          <h4><?= $tipe_labels[$tx['tipe']] ?? $tx['tipe'] ?></h4>
          <p><?= htmlspecialchars($tx['keterangan'] ?: 'Tidak ada keterangan') ?></p>
        </div>
        <div class="tx-right">
          <div class="tx-amount <?= $is_positive?'pos':'neg' ?>">
            <?= $is_positive?'+':'-' ?>Rp <?= number_format($tx['jumlah'],0,',','.') ?>
          </div>
          <div class="tx-time"><?= date('H:i', strtotime($tx['created_at'])) ?></div>
        </div>
      </a>
      <?php endwhile; ?>
      <?php if ($count === 0): ?>
      <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <h3>Belum Ada Transaksi</h3>
        <p>Transaksi Anda akan muncul di sini.</p>
      </div>
      <?php endif; ?>
    </div>
    <div style="height:20px"></div>
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
<script>
function setFilter(f) { window.location.href = 'mutasi.php?filter=' + f; }
</script>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
