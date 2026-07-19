<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$user = $koneksi->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$inisial = strtoupper(substr($user['nama'],0,1));
$tx_count = $koneksi->query("SELECT COUNT(*) as c FROM transaksi WHERE user_id=$user_id")->fetch_assoc()['c'];
$level_icons = ['Basic'=>'fa-circle','Silver'=>'fa-star-half-stroke','Gold'=>'fa-star','Platinum'=>'fa-gem'];
$li = $level_icons[$user['member_level']] ?? 'fa-star';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Profil</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/mobile.css">
<style>
.profile-hero {
  background: linear-gradient(135deg, #6C3CE1 0%, #9B72EF 55%, #00D4FF 100%);
  padding: 28px 20px 40px; text-align: center; position: relative;
  border-bottom-left-radius: 36px; border-bottom-right-radius: 36px;
  box-shadow: 0 24px 60px rgba(108,60,225,0.24);
  overflow: hidden;
}
.profile-hero::before {
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(circle at 20% 15%, rgba(255,255,255,0.22), transparent 18%),
              radial-gradient(circle at 80% 30%, rgba(255,255,255,0.14), transparent 14%);
  pointer-events: none;
}
.profile-hero::after {
  content: '';
  position: absolute;
  bottom: -20px; left: 50%; transform: translateX(-50%);
  width: 120%; height: 120px; background: rgba(255,255,255,0.08);
  border-radius: 50%; filter: blur(18px);
  pointer-events: none;
}
.avatar-wrap {
  position: relative; display: inline-block; z-index: 1;
}
.avatar-img {
  width: 96px; height: 96px; border-radius: 50%;
  border: 3px solid rgba(255,255,255,0.4);
  background: rgba(255,255,255,0.16);
  display: flex; align-items: center; justify-content: center;
  font-size: 36px; font-weight: 700; color: #fff;
  margin: 0 auto; overflow: hidden;
  box-shadow: 0 18px 35px rgba(0,0,0,0.14);
}
.avatar-img img { width: 100%; height: 100%; object-fit: cover; }
.avatar-edit-btn {
  position: absolute; bottom: 0; right: -4px;
  width: 30px; height: 30px; border-radius: 50%;
  background: #fff; border: 2px solid rgba(255,255,255,0.85);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: #6C3CE1; font-size: 12px;
  box-shadow: 0 10px 24px rgba(0,0,0,0.16);
}
.profile-name { font-size: 22px; font-weight: 700; margin-top: 16px; color: var(--text-primary); }
.profile-email { font-size: 14px; color: var(--text-secondary); margin-top: 6px; }
.member-badge-hero {
  display: inline-flex; align-items: center; gap: 7px;
  background: rgba(255,255,255,0.18); border-radius: 24px;
  padding: 8px 16px; margin-top: 14px; font-size: 12px; font-weight: 700;
  color: var(--text-primary); border: 1px solid rgba(255,255,255,0.16);
  backdrop-filter: blur(10px);
}

.profile-card-up {
  margin: -34px 18px 0; background: var(--surface);
  border: 1px solid var(--border); border-radius: 28px;
  padding: 26px 22px 22px; position: relative; z-index: 10;
  box-shadow: 0 24px 60px rgba(0,0,0,0.18);
}
.balance-display {
  display: flex; justify-content: space-between; align-items: center;
  padding: 14px 0; border-bottom: 1px solid var(--border);
}
.balance-display:last-child { border-bottom: none; }
.balance-label { font-size: 12px; color: var(--text-muted); }
.balance-val { font-size: 16px; font-weight: 700; color: #10B981; }

.info-list { padding: 0 16px; margin-top: 18px; }
.info-item {
  display: flex; align-items: center; gap: 14px;
  padding: 14px; background: var(--card); border: 1px solid var(--border);
  border-radius: 18px; margin-bottom: 10px;
}
.info-icon { width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.info-icon.purple { background: rgba(108,60,225,0.18); color: #B49CFF; }
.info-icon.cyan { background: rgba(0,212,255,0.16); color: #8BE8FF; }
.info-icon.green { background: rgba(16,185,129,0.18); color: #8CEAC7; }
.info-icon.orange { background: rgba(251,191,36,0.18); color: #FED57B; }
.info-icon.pink { background: rgba(236,72,153,0.18); color: #F59ACE; }
.info-content { flex: 1; }
.info-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px; }
.info-value { font-size: 14px; font-weight: 600; color: var(--text-primary); }

.menu-section { padding: 0 16px; margin-top: 18px; }
.menu-btn {
  display: flex; align-items: center; gap: 14px;
  padding: 14px; background: var(--card); border: 1px solid var(--border);
  border-radius: 18px; margin-bottom: 10px; cursor: pointer;
  text-decoration: none; color: var(--text-primary); transition: all 0.2s ease;
  width: 100%;
}
.menu-btn:hover { background: var(--card-hover); border-color: rgba(108,60,225,0.25); }
.menu-btn i:first-child { width: 44px; height: 44px; background: rgba(255,255,255,0.08); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 16px; color: var(--text-primary); }
.menu-btn span { flex: 1; font-size: 14px; font-weight: 600; text-align: left; }
.menu-btn .chevron { color: var(--text-secondary); font-size: 12px; }
.menu-btn.danger { color: #EF4444; }
.menu-btn.danger i:first-child { background: rgba(239,68,68,0.14); color: #EF4444; }
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
    <div class="profile-hero animate-up">
      <div class="avatar-wrap">
        <div class="avatar-img">
          <?php if ($user['foto'] && $user['foto'] !== 'default.png'): ?>
            <img src="../assets/uploads/profile/<?= htmlspecialchars($user['foto']) ?>" alt="">
          <?php else: ?>
            <?= $inisial ?>
          <?php endif; ?>
        </div>
        <div class="avatar-edit-btn" onclick="editPhoto()"><i class="fas fa-camera"></i></div>
      </div>
      <div class="profile-name"><?= htmlspecialchars($user['nama']) ?></div>
      <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
      <div class="member-badge-hero">
        <i class="fas <?= $li ?>"></i>
        <?= $user['member_level'] ?> Member
      </div>
    </div>

    <!-- Balance Card -->
    <div class="profile-card-up animate-up-1">
      <div class="balance-display">
        <div>
          <div class="balance-label">Total Saldo</div>
          <div class="balance-val">Rp <?= number_format($user['saldo'],0,',','.') ?></div>
        </div>
        <a href="topup.php" style="background:linear-gradient(135deg,#6C3CE1,#9B72EF);color:#fff;border:none;border-radius:12px;padding:8px 16px;font-size:12px;font-weight:700;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:6px;">
          <i class="fas fa-plus"></i> Top Up
        </a>
      </div>
      <div class="balance-display">
        <div><div class="balance-label">Member ID</div><div style="font-size:14px;font-weight:700;font-family:monospace"><?= $user['member_id'] ?></div></div>
        <div style="text-align:right"><div class="balance-label">Transaksi</div><div style="font-size:14px;font-weight:700;color:var(--accent)"><?= $tx_count ?>x</div></div>
      </div>
    </div>

    <!-- Info -->
    <div class="info-list animate-up-2">
      <div class="info-item">
        <div class="info-icon purple"><i class="fas fa-user"></i></div>
        <div class="info-content"><div class="info-label">Nama Lengkap</div><div class="info-value"><?= htmlspecialchars($user['nama']) ?></div></div>
      </div>
      <div class="info-item">
        <div class="info-icon cyan"><i class="fas fa-id-card"></i></div>
        <div class="info-content"><div class="info-label">NIS/NIM</div><div class="info-value"><?= htmlspecialchars($user['nis_nim']??'-') ?></div></div>
      </div>
      <div class="info-item">
        <div class="info-icon green"><i class="fas fa-school"></i></div>
        <div class="info-content"><div class="info-label">Kelas/Jabatan</div><div class="info-value"><?= htmlspecialchars($user['kelas']??'-') ?></div></div>
      </div>
      <div class="info-item">
        <div class="info-icon orange"><i class="fas fa-phone"></i></div>
        <div class="info-content"><div class="info-label">No. HP</div><div class="info-value"><?= htmlspecialchars($user['no_hp']??'-') ?></div></div>
      </div>
      <div class="info-item">
        <div class="info-icon pink"><i class="fas fa-users"></i></div>
        <div class="info-content"><div class="info-label">Role</div><div class="info-value" style="text-transform:capitalize"><?= $user['role'] ?></div></div>
      </div>
    </div>

    <!-- Menu -->
    <div class="menu-section animate-up-3">
      <a href="settings.php" class="menu-btn">
        <i class="fas fa-user-pen"></i>
        <span>Edit Profil</span>
        <i class="fas fa-chevron-right chevron"></i>
      </a>
      <a href="notifikasi.php" class="menu-btn">
        <i class="fas fa-bell"></i>
        <span>Notifikasi</span>
        <i class="fas fa-chevron-right chevron"></i>
      </a>
      <a href="mutasi.php" class="menu-btn">
        <i class="fas fa-receipt"></i>
        <span>Riwayat Transaksi</span>
        <i class="fas fa-chevron-right chevron"></i>
      </a>
      <button class="menu-btn danger" onclick="confirmLogout()">
        <i class="fas fa-right-from-bracket"></i>
        <span>Keluar</span>
        <i class="fas fa-chevron-right chevron"></i>
      </button>
    </div>

    <div style="height:20px"></div>
  </div>

  <div class="bottom-nav">
    <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="mutasi.php" class="nav-item"><i class="fas fa-receipt"></i><span>Mutasi</span></a>
    <a href="qr.php" class="nav-qr"><i class="fas fa-qrcode"></i></a>
    <a href="merchant.php" class="nav-item"><i class="fas fa-store"></i><span>Merchant</span></a>
    <a href="profile.php" class="nav-item active"><i class="fas fa-user"></i><span>Profil</span></a>
  </div>
</div>
</div>

<script>
function confirmLogout() {
  Swal.fire({
    title: 'Keluar dari JAXPAY?',
    text: 'Anda akan keluar dari akun ini.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Keluar',
    cancelButtonText: 'Batal',
    background: '#1A1A2E', color: '#fff',
    confirmButtonColor: '#EF4444', cancelButtonColor: '#374151'
  }).then(r => { if (r.isConfirmed) window.location.href = '../auth/logout.php'; });
}
function editPhoto() {
  Swal.fire({icon:'info',title:'Edit Foto',html:'Pergi ke <strong>Edit Profil</strong> untuk mengganti foto.',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1',confirmButtonText:'Edit Profil'}).then(r=>{ if(r.isConfirmed) window.location.href='settings.php'; });
}
</script>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
