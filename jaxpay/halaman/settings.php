<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
$user_id = $_SESSION['user_id'];
$user = $koneksi->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$inisial = strtoupper(substr($user['nama'],0,1));
$msg = '';
if (isset($_SESSION['msg'])) { $msg = $_SESSION['msg']; unset($_SESSION['msg']); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Pengaturan</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/mobile.css?v=5">
<style>
.settings-section { padding: 0 16px; margin-bottom: 16px; }
.settings-section h3 { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; padding-left: 4px; }
.photo-edit {
  display: flex; align-items: center; gap: 16px;
  background: var(--card); border: 1px solid var(--border); border-radius: 16px;
  padding: 16px; margin-bottom: 16px; cursor: pointer;
}
.photo-avatar { width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg,var(--primary),var(--accent)); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; color: #fff; overflow: hidden; flex-shrink: 0; }
.photo-avatar img { width: 100%; height: 100%; object-fit: cover; }
.photo-info h4 { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
.photo-info p { font-size: 12px; color: var(--text-muted); }
.photo-input { display: none; }
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
      <a href="profile.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
      <div class="page-title">Edit Profil</div>
    </div>

    <form id="profileForm" enctype="multipart/form-data" style="margin-top:16px">

      <!-- Photo -->
      <div class="settings-section animate-up-1">
        <h3>Foto Profil</h3>
        <div class="photo-edit" onclick="document.getElementById('photoInput').click()">
          <div class="photo-avatar" id="avatarPreview">
            <?php if ($user['foto'] && $user['foto'] !== 'default.png'): ?>
              <img src="../assets/uploads/profile/<?= htmlspecialchars($user['foto']) ?>" id="avatarImg">
            <?php else: ?>
              <?= $inisial ?>
            <?php endif; ?>
          </div>
          <div class="photo-info">
            <h4>Ganti Foto Profil</h4>
            <p>Ketuk untuk memilih foto baru (JPG/PNG, max 2MB)</p>
          </div>
          <i class="fas fa-camera" style="color:var(--primary-light);margin-left:auto"></i>
        </div>
        <input type="file" id="photoInput" name="foto" accept="image/*" class="photo-input" onchange="previewPhoto(this)">
      </div>

      <!-- Personal Info -->
      <div class="settings-section animate-up-2">
        <h3>Informasi Pribadi</h3>
        <div class="form-group">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($user['nama']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:0.5;cursor:not-allowed">
          <small style="color:var(--text-muted);font-size:11px;padding-left:4px">Email tidak dapat diubah</small>
        </div>
        <div class="form-group">
          <label class="form-label">No. HP</label>
          <input type="tel" name="no_hp" class="form-input" value="<?= htmlspecialchars($user['no_hp']??'') ?>" placeholder="08xxxxxxxxxx">
        </div>
      </div>

      <!-- School Info (read only) -->
      <div class="settings-section animate-up-3">
        <h3>Info Akademik</h3>
        <div class="form-group">
          <label class="form-label">NIS/NIM</label>
          <input type="text" class="form-input" value="<?= htmlspecialchars($user['nis_nim']??'') ?>" disabled style="opacity:0.5;cursor:not-allowed">
        </div>
        <div class="form-group">
          <label class="form-label">Kelas / Jabatan</label>
          <input type="text" class="form-input" value="<?= htmlspecialchars($user['kelas']??'') ?>" disabled style="opacity:0.5;cursor:not-allowed">
        </div>
        <small style="color:var(--text-muted);font-size:11px;display:block;padding:0 4px">Info akademik hanya dapat diubah oleh Admin.</small>
      </div>

      <div style="padding:0 16px 20px" class="animate-up-4">
        <button type="submit" class="btn-primary" id="btnSave">
          <i class="fas fa-check"></i> Simpan Perubahan
        </button>
      </div>
    </form>

    <!-- Theme Toggle Section -->
    <div class="settings-section animate-up-4" style="margin-top:4px">
      <h3>Tampilan</h3>
      <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:16px;display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:40px;height:40px;border-radius:12px;background:rgba(108,60,225,0.15);display:flex;align-items:center;justify-content:center;color:var(--primary-light);font-size:18px">
            <i class="fas fa-circle-half-stroke"></i>
          </div>
          <div>
            <div style="font-size:14px;font-weight:700;color:var(--text-primary)">Mode Tampilan</div>
            <div style="font-size:12px;color:var(--text-secondary)" id="themeLabel">Mode Gelap</div>
          </div>
        </div>
        <!-- Toggle Switch -->
        <label style="position:relative;display:inline-block;width:52px;height:28px;cursor:pointer">
          <input type="checkbox" id="themeSwitch" style="opacity:0;width:0;height:0;position:absolute" onchange="switchTheme(this)">
          <span id="themeSwitchTrack" style="position:absolute;inset:0;border-radius:28px;background:var(--primary);transition:all 0.3s;display:flex;align-items:center;padding:3px">
            <span id="themeSwitchThumb" style="width:22px;height:22px;border-radius:50%;background:#fff;transition:transform 0.3s;display:flex;align-items:center;justify-content:center;font-size:11px">🌙</span>
          </span>
        </label>
      </div>
    </div>
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
function switchTheme(checkbox) {
  if (checkbox.checked) {
    ThemeManager.apply('light');
    document.getElementById('themeLabel').textContent = 'Mode Terang';
    document.getElementById('themeSwitchThumb').textContent = '☀️';
    document.getElementById('themeSwitchThumb').style.transform = 'translateX(24px)';
  } else {
    ThemeManager.apply('dark');
    document.getElementById('themeLabel').textContent = 'Mode Gelap';
    document.getElementById('themeSwitchThumb').textContent = '🌙';
    document.getElementById('themeSwitchThumb').style.transform = 'translateX(0)';
  }
}

// Sync switch state on page load
document.addEventListener('DOMContentLoaded', function() {
  const saved = ThemeManager.getSaved();
  const sw = document.getElementById('themeSwitch');
  const label = document.getElementById('themeLabel');
  const thumb = document.getElementById('themeSwitchThumb');
  if (saved === 'light') {
    sw.checked = true;
    label.textContent = 'Mode Terang';
    thumb.textContent = '☀️';
    thumb.style.transform = 'translateX(24px)';
  } else {
    sw.checked = false;
    label.textContent = 'Mode Gelap';
    thumb.textContent = '🌙';
    thumb.style.transform = 'translateX(0)';
  }
});

function previewPhoto(input) {
  const file = input.files[0];
  if (!file) return;
  if (file.size > 2*1024*1024) { Swal.fire({icon:'error',title:'File Terlalu Besar',text:'Maksimal 2MB',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'}); return; }
  const reader = new FileReader();
  reader.onload = e => {
    const av = document.getElementById('avatarPreview');
    av.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`;
  };
  reader.readAsDataURL(file);
}

document.getElementById('profileForm').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('btnSave');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Menyimpan...';
  Swal.fire({title:'Menyimpan...',allowOutsideClick:false,didOpen:()=>Swal.showLoading(),background:'#1A1A2E',color:'#fff'});

  const fd = new FormData(e.target);
  const resp = await fetch('../proses/update_profile.php', {method:'POST', body:fd});
  const data = await resp.json();

  if (data.success) {
    Swal.fire({icon:'success',title:'Profil Diperbarui!',text:'Perubahan berhasil disimpan.',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1',timer:2000,showConfirmButton:false})
      .then(()=>window.location.href='profile.php');
  } else {
    Swal.fire({icon:'error',title:'Gagal',text:data.message,background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
    btn.disabled=false; btn.innerHTML='<i class="fas fa-check"></i> Simpan Perubahan';
  }
});
</script>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
