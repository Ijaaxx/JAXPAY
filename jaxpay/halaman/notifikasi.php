<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
$user_id = $_SESSION['user_id'];

// Mark all read
if (isset($_GET['markread'])) {
  $koneksi->query("UPDATE notifications SET is_read=1 WHERE user_id=$user_id");
  header('Location: notifikasi.php');
  exit;
}

$notifs = $koneksi->query("SELECT * FROM notifications WHERE user_id=$user_id ORDER BY created_at DESC");
$notif_icons = ['topup'=>'fa-plus','transfer'=>'fa-paper-plane','pembayaran'=>'fa-store','promo'=>'fa-tag','info'=>'fa-info'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Notifikasi</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/mobile.css">
<style>
.notif-item {
  display: flex; gap: 12px; padding: 14px; background: var(--card);
  border: 1px solid var(--border); border-radius: 14px; margin-bottom: 8px;
  cursor: pointer; transition: all 0.2s; position: relative;
}
.notif-item.unread { background: rgba(108,60,225,0.08); border-color: rgba(108,60,225,0.25); }
.notif-item:hover { background: var(--card-hover); }
.unread-dot {
  width: 8px; height: 8px; border-radius: 50%; background: var(--primary-light);
  position: absolute; top: 14px; right: 14px; flex-shrink: 0;
}
.notif-icon { width: 44px; height: 44px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.notif-icon.topup { background: rgba(16,185,129,0.15); color: #10B981; }
.notif-icon.transfer { background: rgba(0,212,255,0.15); color: #00D4FF; }
.notif-icon.pembayaran { background: rgba(108,60,225,0.15); color: #9B72EF; }
.notif-icon.promo { background: rgba(251,191,36,0.15); color: #FBBF24; }
.notif-icon.info { background: rgba(107,114,128,0.15); color: #9CA3AF; }
.notif-content { flex: 1; padding-right: 16px; }
.notif-title { font-size: 13px; font-weight: 700; margin-bottom: 4px; }
.notif-msg { font-size: 12px; color: var(--text-muted); line-height: 1.5; }
.notif-time { font-size: 10px; color: var(--text-muted); margin-top: 6px; }
.mark-all-btn {
  background: none; border: 1px solid var(--border); border-radius: 10px;
  padding: 6px 12px; color: var(--primary-light); font-size: 12px; font-weight: 600;
  cursor: pointer; font-family: inherit;
}
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
    <div class="page-header animate-up" style="justify-content:space-between">
      <div style="display:flex;align-items:center;gap:14px">
        <a href="home.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
        <div class="page-title">Notifikasi</div>
      </div>
      <button class="mark-all-btn" onclick="window.location='notifikasi.php?markread=1'">Baca Semua</button>
    </div>

    <div style="padding:14px 16px 0" class="animate-up-2">
      <?php
      $count = 0;
      while ($n = $notifs->fetch_assoc()):
        $count++;
        $icon = $notif_icons[$n['tipe']] ?? 'fa-bell';
        $unread = !$n['is_read'];
        $timeago = '';
        $diff = time() - strtotime($n['created_at']);
        if ($diff < 60) $timeago = 'Baru saja';
        elseif ($diff < 3600) $timeago = floor($diff/60) . ' menit lalu';
        elseif ($diff < 86400) $timeago = floor($diff/3600) . ' jam lalu';
        else $timeago = date('d M Y', strtotime($n['created_at']));
      ?>
      <div class="notif-item <?= $unread?'unread':'' ?>" onclick="markRead(<?= $n['id'] ?>)">
        <?php if ($unread): ?><div class="unread-dot"></div><?php endif; ?>
        <div class="notif-icon <?= $n['tipe'] ?>"><i class="fas <?= $icon ?>"></i></div>
        <div class="notif-content">
          <div class="notif-title"><?= htmlspecialchars($n['judul']) ?></div>
          <div class="notif-msg"><?= htmlspecialchars($n['pesan']) ?></div>
          <div class="notif-time"><?= $timeago ?></div>
        </div>
      </div>
      <?php endwhile; ?>
      <?php if ($count === 0): ?>
      <div class="empty-state">
        <i class="fas fa-bell-slash"></i>
        <h3>Tidak Ada Notifikasi</h3>
        <p>Notifikasi Anda akan muncul di sini.</p>
      </div>
      <?php endif; ?>
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
function markRead(id) {
  fetch('../proses/mark_notif.php?id=' + id);
}
</script>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
