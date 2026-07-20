<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $koneksi->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$_SESSION['user'] = $user;

// Count unread notifications
$notif_count = $koneksi->query("SELECT COUNT(*) as c FROM notifications WHERE user_id=$user_id AND is_read=0")->fetch_assoc()['c'];

// Recent transactions
$transaksi = $koneksi->query("SELECT * FROM transaksi WHERE user_id=$user_id ORDER BY created_at DESC LIMIT 5");

$nama_singkat = explode(' ', $user['nama'])[0];
$inisial = strtoupper(substr($user['nama'], 0, 1));

$jam = date('H');
$salam = $jam < 12 ? 'Selamat Pagi' : ($jam < 17 ? 'Selamat Siang' : ($jam < 20 ? 'Selamat Sore' : 'Selamat Malam'));

$level_color = ['Basic'=>'#6B7280','Silver'=>'#94A3B8','Gold'=>'#FFB800','Platinum'=>'#00D4FF'];
$level_icon = ['Basic'=>'fa-circle','Silver'=>'fa-star-half-stroke','Gold'=>'fa-star','Platinum'=>'fa-gem'];
$lc = $level_color[$user['member_level']] ?? '#94A3B8';
$li = $level_icon[$user['member_level']] ?? 'fa-star';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Home</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/mobile.css?v=5">
<style>
/* HOME SPECIFIC STYLES */
.balance-card {
  margin: 16px;
  background: linear-gradient(135deg, #6C3CE1 0%, #9B72EF 50%, #00D4FF 100%);
  border-radius: 24px;
  padding: 24px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(108,60,225,0.4);
}
.balance-card::before {
  content: '';
  position: absolute;
  top: -40%; right: -20%;
  width: 200px; height: 200px;
  border-radius: 50%;
  background: rgba(255,255,255,0.08);
}
.balance-card::after {
  content: '';
  position: absolute;
  bottom: -30%; left: -10%;
  width: 160px; height: 160px;
  border-radius: 50%;
  background: rgba(0,0,0,0.1);
}
.card-header-row { display: flex; justify-content: space-between; align-items: flex-start; position: relative; z-index: 1; }
.card-label { font-size: 12px; color: rgba(255,255,255,0.7); font-weight: 600; letter-spacing: 1px; }
.member-badge {
  display: flex; align-items: center; gap: 6px;
  background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);
  border-radius: 20px; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.2);
}
.member-badge i { font-size: 12px; }
.member-badge span { font-size: 11px; font-weight: 700; }
.balance-amount {
  font-size: 32px; font-weight: 800;
  color: #fff; margin: 12px 0 4px;
  letter-spacing: -0.5px;
  position: relative; z-index: 1;
  transition: all 0.5s;
}
.balance-amount.blur { filter: blur(8px); }
.balance-toggle {
  background: rgba(255,255,255,0.2); border: none; border-radius: 8px;
  padding: 4px 8px; cursor: pointer; color: #fff; font-size: 13px;
  display: flex; align-items: center; gap: 5px; position: relative; z-index: 1;
}
.member-id { font-size: 12px; color: rgba(255,255,255,0.65); position: relative; z-index: 1; }
.card-bottom-row {
  display: flex; justify-content: space-between; align-items: flex-end;
  margin-top: 20px; position: relative; z-index: 1;
}
.card-bottom-row .avatar {
  width: 36px; height: 36px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.4);
  background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 700;
}

/* Greeting */
.greeting-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 16px 20px 8px;
}
.greeting-text { font-size: 22px; font-weight: 800; }
.greeting-sub { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
.notif-btn {
  width: 42px; height: 42px; border-radius: 14px;
  background: var(--card); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; text-decoration: none; color: var(--text-primary); position: relative;
  transition: all 0.2s;
}
.notif-btn:hover { background: var(--card-hover); }
.notif-btn i { color: inherit; }
.notif-badge {
  position: absolute; top: -4px; right: -4px;
  width: 18px; height: 18px; border-radius: 50%;
  background: #EF4444; font-size: 10px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid #0A0A15;
}

/* Quick Menu */
.section-title { font-size: 15px; font-weight: 700; padding: 16px 20px 12px; }
.quick-menu { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; padding: 0 16px 8px; }
.menu-item {
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  cursor: pointer; text-decoration: none; transition: all 0.2s;
}
.menu-item:active { transform: scale(0.92); }
.menu-icon {
  width: 56px; height: 56px; border-radius: 18px;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; color: #fff; transition: all 0.3s;
}
.menu-icon.purple { background: linear-gradient(135deg, #6C3CE1, #9B72EF); box-shadow: 0 4px 16px rgba(108,60,225,0.4); }
.menu-icon.cyan { background: linear-gradient(135deg, #0891B2, #00D4FF); box-shadow: 0 4px 16px rgba(0,212,255,0.3); }
.menu-icon.green { background: linear-gradient(135deg, #059669, #10B981); box-shadow: 0 4px 16px rgba(16,185,129,0.3); }
.menu-icon.orange { background: linear-gradient(135deg, #D97706, #FBBF24); box-shadow: 0 4px 16px rgba(251,191,36,0.3); }
.menu-icon.pink { background: linear-gradient(135deg, #BE185D, #EC4899); box-shadow: 0 4px 16px rgba(236,72,153,0.3); }
.menu-icon.teal { background: linear-gradient(135deg, #0F766E, #14B8A6); box-shadow: 0 4px 16px rgba(20,184,166,0.3); }
.menu-icon.indigo { background: linear-gradient(135deg, #4338CA, #6366F1); box-shadow: 0 4px 16px rgba(99,102,241,0.3); }
.menu-icon.gray { background: linear-gradient(135deg, #374151, #6B7280); box-shadow: 0 4px 16px rgba(107,114,128,0.3); }
.menu-item span { font-size: 11px; font-weight: 600; color: var(--text-muted); text-align: center; }
.menu-item:hover .menu-icon { transform: translateY(-3px); }

/* Transaction List */
.tx-list { padding: 0 16px; }
.tx-item {
  display:flex; gap:12px; align-items:center; padding:14px; border-radius:14px;
  background: var(--card); border: 1px solid var(--border); box-shadow: var(--tx-shadow);
  transition:transform .18s ease, box-shadow .18s ease; cursor:default;
}
.tx-item + .tx-item { margin-top:10px; }
.tx-item:hover { transform:translateY(-4px); box-shadow: var(--tx-shadow-hover); }
.tx-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.tx-icon.purple { background: linear-gradient(135deg, rgba(108,60,225,0.18), rgba(155,114,239,0.12)); color:#B49CFF; }
.tx-icon.green { background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(16,185,129,0.06)); color:#8CEAC7; }
.tx-icon.red { background: linear-gradient(135deg, rgba(239,68,68,0.12), rgba(239,68,68,0.06)); color:#FFB6B6; }
.tx-body { flex:1; }
.tx-title { font-size:15px; font-weight:700; color:var(--text-primary); }
.tx-sub { font-size:13px; color:var(--text-secondary); margin-top:4px; }
.tx-amount { font-weight:800; color:var(--danger); }
.tx-amount.plus { color:var(--success); }
.tx-item {
  display: flex; align-items: center; gap: 14px;
  padding: 14px 0; border-bottom: 1px solid var(--border);
  cursor: pointer; transition: all 0.2s;
}
.tx-item:last-child { border-bottom: none; }
.tx-item:hover { background: rgba(255,255,255,0.03); border-radius: 12px; padding-left: 8px; }
.tx-icon {
  width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.tx-icon.topup { background: rgba(16,185,129,0.15); color: #10B981; }
.tx-icon.out { background: rgba(239,68,68,0.15); color: #EF4444; }
.tx-icon.in { background: rgba(0,212,255,0.15); color: #00D4FF; }
.tx-icon.pay { background: rgba(108,60,225,0.15); color: #9B72EF; }
.tx-icon.qr { background: rgba(251,191,36,0.15); color: #FBBF24; }
.tx-info { flex: 1; }
.tx-info h4 { font-size: 14px; font-weight: 600; color: #fff; margin-bottom: 3px; }
.tx-info p { font-size: 12px; color: var(--text-muted); }
.tx-amount { text-align: right; }
.tx-amount .amount { font-size: 14px; font-weight: 700; }
.tx-amount .amount.positive { color: #10B981; }
.tx-amount .amount.negative { color: #EF4444; }
.tx-amount .time { font-size: 11px; color: var(--text-muted); margin-top: 3px; }

/* Promo Banner */
.promo-scroll { display: flex; gap: 12px; padding: 0 16px; overflow-x: auto; scrollbar-width: none; margin-bottom: 4px; }
.promo-scroll::-webkit-scrollbar { display: none; }
.promo-card {
  flex-shrink: 0; width: 240px; height: 100px; border-radius: 18px;
  padding: 16px; position: relative; overflow: hidden; cursor: pointer;
}
.promo-card.purple { background: linear-gradient(135deg, #5028C5, #6C3CE1); }
.promo-card.teal { background: linear-gradient(135deg, #0F766E, #00D4FF); }
.promo-card h4 { font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 4px; }
.promo-card p { font-size: 11px; color: rgba(255,255,255,0.7); }
.promo-tag {
  position: absolute; right: 12px; top: 12px;
  background: rgba(255,255,255,0.2); border-radius: 8px;
  padding: 4px 8px; font-size: 10px; font-weight: 700; color: #fff;
}
</style>
  <link rel="stylesheet" href="../assets/css/theme.css?v=4">
  <script src="../assets/js/theme.js?v=3"></script>
</head>
<body>

<div class="phone-outer">
<div class="phone-frame">

  <div class="dynamic-island">
    <div class="di-speaker"></div>
    <div class="di-camera"></div>
  </div>

  <div class="status-bar">
    <span>9:41</span>
    <div class="status-right">
      <i class="fas fa-signal"></i>
      <i class="fas fa-wifi"></i>
      <i class="fas fa-battery-full"></i>
    </div>
  </div>

  <div class="phone-content">

    <!-- Greeting -->
    <div class="greeting-row animate-up">
      <div>
        <div class="greeting-text">👋 <?= $salam ?>,</div>
        <div class="greeting-text" style="color:var(--primary-light)"><?= $nama_singkat ?>!</div>
        <div class="greeting-sub"><?= date('l, d F Y') ?></div>
      </div>
      <a href="notifikasi.php" class="notif-btn">
        <i class="fas fa-bell"></i>
        <?php if($notif_count > 0): ?>
        <div class="notif-badge"><?= $notif_count > 9 ? '9+' : $notif_count ?></div>
        <?php endif; ?>
      </a>
    </div>

    <!-- Balance Card -->
    <div class="balance-card animate-up-1">
      <div class="card-header-row">
        <div>
          <div class="card-label">TOTAL SALDO</div>
          <div class="balance-amount" id="balanceDisplay">
            Rp <?= number_format($user['saldo'], 0, ',', '.') ?>
          </div>
          <div class="member-id"><?= $user['member_id'] ?></div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
          <div class="member-badge" style="background:rgba(255,255,255,0.15);color:#fff;">
            <i class="fas <?= $li ?>" style="color:<?= $lc ?>"></i>
            <span><?= $user['member_level'] ?></span>
          </div>
          <button class="balance-toggle" id="balanceToggle" onclick="toggleBalance()">
            <i class="fas fa-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>
      <div class="card-bottom-row">
        <div>
          <div style="font-size:11px;color:rgba(255,255,255,0.6);">Role</div>
          <div style="font-size:13px;font-weight:700;text-transform:capitalize"><?= $user['role'] ?></div>
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
          <div class="avatar"><?= $inisial ?></div>
          <div style="font-size:13px;font-weight:700;"><?= $nama_singkat ?></div>
        </div>
      </div>
    </div>

    <!-- Quick Menu -->
    <div class="section-title animate-up-2">Layanan</div>
    <div class="quick-menu animate-up-2">
      <a href="topup.php" class="menu-item">
        <div class="menu-icon green"><i class="fas fa-plus"></i></div>
        <span>Top Up</span>
      </a>
      <a href="transfer.php" class="menu-item">
        <div class="menu-icon cyan"><i class="fas fa-paper-plane"></i></div>
        <span>Transfer</span>
      </a>
      <a href="qr.php" class="menu-item">
        <div class="menu-icon purple"><i class="fas fa-qrcode"></i></div>
        <span>QR Pay</span>
      </a>
      <a href="merchant.php" class="menu-item">
        <div class="menu-icon orange"><i class="fas fa-store"></i></div>
        <span>Merchant</span>
      </a>
      <a href="mutasi.php" class="menu-item">
        <div class="menu-icon indigo"><i class="fas fa-list-ul"></i></div>
        <span>Mutasi</span>
      </a>
      <a href="scan.php" class="menu-item">
        <div class="menu-icon teal"><i class="fas fa-camera"></i></div>
        <span>Scan QR</span>
      </a>
      <a href="profile.php" class="menu-item">
        <div class="menu-icon pink"><i class="fas fa-user"></i></div>
        <span>Profil</span>
      </a>
      <a href="settings.php" class="menu-item">
        <div class="menu-icon gray"><i class="fas fa-gear"></i></div>
        <span>Lainnya</span>
      </a>
    </div>

    <!-- Promo Banner -->
    <div class="section-title animate-up-3">Promo</div>
    <div class="promo-scroll animate-up-3">
      <div class="promo-card purple" onclick="showPromo()">
        <div class="promo-tag">HOT 🔥</div>
        <h4>Cashback 10%</h4>
        <p>Bayar di merchant JAXPAY<br>periode Mei 2025</p>
      </div>
      <div class="promo-card teal" onclick="showPromo()">
        <div class="promo-tag">NEW</div>
        <h4>Transfer Gratis</h4>
        <p>0 biaya transfer antar<br>pengguna JAXPAY</p>
      </div>
    </div>

    <!-- Transactions -->
    <div class="section-title animate-up-4" style="display:flex;justify-content:space-between;align-items:center;padding-right:20px;">
      Transaksi Terakhir
      <a href="mutasi.php" style="font-size:12px;color:var(--primary-light);font-weight:600;text-decoration:none">Lihat Semua</a>
    </div>
    <div class="tx-list animate-up-4">
      <?php
      $tipe_labels = [
        'topup'=>'Top Up Saldo','transfer_masuk'=>'Terima Transfer',
        'transfer_keluar'=>'Kirim Transfer','pembayaran'=>'Pembayaran',
        'qr_payment'=>'QR Payment'
      ];
      $tipe_icon = [
        'topup'=>['topup','fa-plus'],'transfer_masuk'=>['in','fa-arrow-down'],
        'transfer_keluar'=>['out','fa-arrow-up'],'pembayaran'=>['pay','fa-store'],
        'qr_payment'=>['qr','fa-qrcode']
      ];
      while ($tx = $transaksi->fetch_assoc()):
        $is_positive = in_array($tx['tipe'], ['topup','transfer_masuk']);
        $ic = $tipe_icon[$tx['tipe']] ?? ['pay','fa-receipt'];
      ?>
      <div class="tx-item" onclick="window.location='detail_transaksi.php?id=<?= $tx['id'] ?>'">
        <div class="tx-icon <?= $ic[0] ?>"><i class="fas <?= $ic[1] ?>"></i></div>
        <div class="tx-info">
          <h4><?= $tipe_labels[$tx['tipe']] ?? $tx['tipe'] ?></h4>
          <p><?= htmlspecialchars($tx['keterangan'] ?: '-') ?></p>
        </div>
        <div class="tx-amount">
          <div class="amount <?= $is_positive ? 'positive' : 'negative' ?>">
            <?= $is_positive ? '+' : '-' ?>Rp <?= number_format($tx['jumlah'],0,',','.') ?>
          </div>
          <div class="time"><?= date('d M', strtotime($tx['created_at'])) ?></div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <div style="height:20px"></div>
  </div><!-- end phone-content -->

  <!-- Bottom Navigation -->
  <div class="bottom-nav">
    <a href="home.php" class="nav-item active">
      <i class="fas fa-home"></i>
      <span>Home</span>
    </a>
    <a href="mutasi.php" class="nav-item">
      <i class="fas fa-receipt"></i>
      <span>Mutasi</span>
    </a>
    <a href="qr.php" class="nav-qr">
      <i class="fas fa-qrcode"></i>
    </a>
    <a href="merchant.php" class="nav-item">
      <i class="fas fa-store"></i>
      <span>Merchant</span>
    </a>
    <a href="profile.php" class="nav-item">
      <i class="fas fa-user"></i>
      <span>Profil</span>
    </a>
  </div>

</div><!-- end phone-frame -->
</div><!-- end phone-outer -->

<script>
let balanceHidden = false;
const balanceEl = document.getElementById('balanceDisplay');
const eyeEl = document.getElementById('eyeIcon');

function toggleBalance() {
  balanceHidden = !balanceHidden;
  if (balanceHidden) {
    balanceEl.style.filter = 'blur(8px)';
    eyeEl.className = 'fas fa-eye-slash';
  } else {
    balanceEl.style.filter = '';
    eyeEl.className = 'fas fa-eye';
  }
}

function showPromo() {
  Swal.fire({
    icon: 'info',
    title: '🎉 Promo JAXPAY',
    html: '<p>Promo spesial untuk pengguna JAXPAY!</p><br><p>Cashback 10% berlaku untuk transaksi di merchant terdaftar selama periode Mei 2025.</p>',
    background: '#1A1A2E',
    color: '#fff',
    confirmButtonColor: '#6C3CE1',
    confirmButtonText: 'Keren!'
  });
}

// Scroll reveal
const reveals = document.querySelectorAll('.reveal');
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1 });
reveals.forEach(r => observer.observe(r));
</script>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
