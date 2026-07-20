<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$admin_id   = (int)$_SESSION['admin_id'];
$admin      = $koneksi->query("SELECT * FROM admin WHERE id=$admin_id")->fetch_assoc();
$inisial    = strtoupper(substr($admin['nama'],0,1));

// Stats overview
$total_users     = $koneksi->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_saldo     = $koneksi->query("SELECT COALESCE(SUM(saldo),0) as s FROM users")->fetch_assoc()['s'];
$total_transaksi = $koneksi->query("SELECT COUNT(*) as c FROM transaksi")->fetch_assoc()['c'];
$total_topup_approved = $koneksi->query("SELECT COUNT(*) as c FROM topup WHERE status='approved'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY Admin - Pengaturan</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/admin.css?v=2">
  <link rel="stylesheet" href="../assets/css/theme.css?v=4">

</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'navbar.php'; ?>

<main class="admin-main animate-in">

  <div style="margin-bottom:24px">
    <h2 style="font-size:20px;font-weight:800">Pengaturan Sistem</h2>
    <p style="color:var(--text-muted);font-size:13px;margin-top:3px">Konfigurasi akun admin & sistem JAXPAY</p>
  </div>

  <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;align-items:start">

    <!-- Profile Card -->
    <div>
      <div class="admin-card" style="margin-bottom:18px">
        <div class="admin-card-body padded" style="text-align:center">
          <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;margin:0 auto 16px;box-shadow:0 8px 24px rgba(108,60,225,0.4)">
            <?= $inisial ?>
          </div>
          <div style="font-size:18px;font-weight:800"><?= htmlspecialchars($admin['nama']) ?></div>
          <div style="font-size:13px;color:var(--text-muted);margin-top:4px"><?= htmlspecialchars($admin['email']) ?></div>
          <div style="margin-top:12px">
            <span class="badge badge-primary"><i class="fas fa-shield-halved"></i> Super Admin</span>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="admin-card">
        <div class="admin-card-header"><h3><i class="fas fa-chart-simple" style="color:var(--accent)"></i> Ringkasan Sistem</h3></div>
        <div class="admin-card-body padded" style="padding-top:0">
          <?php
          $stats_items = [
            ['Total User','fas fa-users','var(--primary-light)',$total_users.' orang'],
            ['Total Saldo','fas fa-wallet','var(--success)','Rp '.number_format($total_saldo,0,',','.')],
            ['Total Transaksi','fas fa-receipt','var(--accent)',$total_transaksi.'x'],
            ['Top Up Approved','fas fa-check-circle','var(--warning)',$total_topup_approved.'x'],
          ];
          foreach ($stats_items as [$label,$icon,$color,$val]):
          ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-muted)">
              <i class="<?= $icon ?>" style="color:<?= $color ?>;width:16px"></i><?= $label ?>
            </div>
            <div style="font-size:13px;font-weight:700"><?= $val ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Settings Forms -->
    <div>
      <!-- Change Profile -->
      <div class="admin-card" style="margin-bottom:18px">
        <div class="admin-card-header">
          <h3><i class="fas fa-user-pen" style="color:var(--primary-light)"></i> Edit Profil Admin</h3>
        </div>
        <div class="admin-card-body padded">
          <form id="profileForm">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($admin['nama']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
              </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan Profil</button>
          </form>
        </div>
      </div>

      <!-- Change Password -->
      <div class="admin-card" style="margin-bottom:18px">
        <div class="admin-card-header">
          <h3><i class="fas fa-lock" style="color:var(--warning)"></i> Ganti Password</h3>
        </div>
        <div class="admin-card-body padded">
          <form id="passwordForm">
            <div class="form-group">
              <label class="form-label">Password Baru</label>
              <input type="password" name="new_password" id="newPass" class="form-control" placeholder="Min. 6 karakter" minlength="6" required>
            </div>
            <div class="form-group">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="confirm_password" id="confirmPass" class="form-control" placeholder="Ulangi password baru" required>
            </div>
            <button type="submit" class="btn btn-warning"><i class="fas fa-key"></i> Ganti Password</button>
          </form>
        </div>
      </div>

      <!-- SMTP Config Info -->
      <div class="admin-card" style="margin-bottom:18px">
        <div class="admin-card-header">
          <h3><i class="fas fa-envelope" style="color:var(--info)"></i> Konfigurasi Email (SMTP)</h3>
        </div>
        <div class="admin-card-body padded">
          <div style="background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.2);border-radius:12px;padding:16px;margin-bottom:16px">
            <p style="font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8">
              Konfigurasi SMTP diatur di file <code style="background:rgba(255,255,255,0.1);padding:2px 8px;border-radius:6px;color:#00D4FF">koneksi.php</code>.<br>
              Ubah nilai berikut di file tersebut:
            </p>
          </div>
          <div style="background:var(--surface-3);border-radius:12px;padding:16px;font-family:monospace;font-size:12px;line-height:2;color:#10B981">
            define('SMTP_HOST', 'smtp.gmail.com');<br>
            define('SMTP_USER', '<span style="color:#F59E0B">youremail@gmail.com</span>');<br>
            define('SMTP_PASS', '<span style="color:#F59E0B">your_app_password</span>');<br>
            define('SMTP_PORT', 587);<br>
            define('SMTP_FROM', 'noreply@jaxpay.id');
          </div>
          <p style="font-size:12px;color:var(--text-muted);margin-top:12px">
            <i class="fas fa-info-circle" style="color:var(--info)"></i>
            Gunakan <strong>App Password</strong> Gmail (bukan password utama). Aktifkan 2FA dulu di akun Google Anda.
          </p>
        </div>
      </div>

      <!-- Theme Toggle -->
      <div class="admin-card" style="margin-bottom:18px">
        <div class="admin-card-header">
          <h3><i class="fas fa-circle-half-stroke" style="color:var(--primary-light)"></i> Mode Tampilan</h3>
        </div>
        <div class="admin-card-body padded">
          <p style="font-size:13px;color:var(--admin-muted,rgba(232,232,240,0.5));margin-bottom:18px">
            Pilih tampilan dashboard yang nyaman untuk Anda. Preferensi tersimpan otomatis di browser.
          </p>
          <div style="display:flex;gap:14px">
            <!-- Dark Mode Card -->
            <div id="cardDark" onclick="setTheme('dark')" style="flex:1;border:2px solid var(--primary);border-radius:14px;padding:18px;cursor:pointer;background:rgba(108,60,225,0.12);text-align:center;transition:all 0.2s">
              <div style="font-size:32px;margin-bottom:10px">🌙</div>
              <div style="font-size:14px;font-weight:700;color:var(--admin-text,#E8E8F0)">Mode Gelap</div>
              <div style="font-size:12px;color:var(--admin-muted,rgba(232,232,240,0.5));margin-top:4px">Nyaman di malam hari</div>
              <div id="badgeDark" style="display:inline-block;margin-top:10px;background:var(--primary);color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">✓ Aktif</div>
            </div>
            <!-- Light Mode Card -->
            <div id="cardLight" onclick="setTheme('light')" style="flex:1;border:2px solid var(--admin-border,rgba(255,255,255,0.1));border-radius:14px;padding:18px;cursor:pointer;background:rgba(255,255,255,0.03);text-align:center;transition:all 0.2s">
              <div style="font-size:32px;margin-bottom:10px">☀️</div>
              <div style="font-size:14px;font-weight:700;color:var(--admin-text,#E8E8F0)">Mode Terang</div>
              <div style="font-size:12px;color:var(--admin-muted,rgba(232,232,240,0.5));margin-top:4px">Terang dan bersih</div>
              <div id="badgeLight" style="display:none;margin-top:10px;background:var(--primary);color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">✓ Aktif</div>
            </div>
          </div>
          <p style="font-size:11px;color:var(--admin-muted,rgba(232,232,240,0.5));margin-top:14px;text-align:center">
            <i class="fas fa-keyboard"></i> Shortcut: <kbd style="background:rgba(255,255,255,0.1);padding:2px 7px;border-radius:5px;font-size:11px">Ctrl + Shift + T</kbd>
          </p>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="admin-card" style="border-color:rgba(239,68,68,0.25)">
        <div class="admin-card-header" style="border-color:rgba(239,68,68,0.15)">
          <h3><i class="fas fa-triangle-exclamation" style="color:var(--danger)"></i> Zona Berbahaya</h3>
        </div>
        <div class="admin-card-body padded">
          <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">
            Operasi berikut bersifat permanen dan tidak dapat dibatalkan. Gunakan dengan hati-hati.
          </p>
          <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button class="btn btn-danger" onclick="clearLogs()">
              <i class="fas fa-trash"></i> Hapus Log Aktivitas
            </button>
            <button class="btn btn-danger" onclick="clearNotifs()">
              <i class="fas fa-bell-slash"></i> Hapus Semua Notifikasi
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

</main>
<?php include 'footer.php'; ?>

<script>
function setTheme(theme) {
  if (window.ThemeManager) ThemeManager.apply(theme);
  updateThemeCards(theme);
}

function updateThemeCards(theme) {
  const isDark = theme === 'dark';
  // Dark card
  document.getElementById('cardDark').style.borderColor  = isDark ? 'var(--primary)' : 'var(--admin-border, rgba(255,255,255,0.1))';
  document.getElementById('cardDark').style.background   = isDark ? 'rgba(108,60,225,0.12)' : 'rgba(255,255,255,0.03)';
  document.getElementById('badgeDark').style.display     = isDark ? 'inline-block' : 'none';
  // Light card
  document.getElementById('cardLight').style.borderColor = isDark ? 'var(--admin-border, rgba(255,255,255,0.1))' : 'var(--primary)';
  document.getElementById('cardLight').style.background  = isDark ? 'rgba(255,255,255,0.03)' : 'rgba(108,60,225,0.12)';
  document.getElementById('badgeLight').style.display    = isDark ? 'none' : 'inline-block';
}

document.addEventListener('DOMContentLoaded', function() {
  updateThemeCards(ThemeManager.getSaved());
  window.addEventListener('jaxpay:theme-change', e => updateThemeCards(e.detail.theme));
});

document.getElementById('profileForm').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('action','update_profile');
  const resp = await fetch('ajax_settings.php',{method:'POST',body:new URLSearchParams(fd)});
  const data = await resp.json();
  Swal.fire({icon:data.success?'success':'error',title:data.success?'Profil Diperbarui!':data.message,timer:1800,showConfirmButton:false}).then(()=>{if(data.success)location.reload();});
});

document.getElementById('passwordForm').addEventListener('submit', async e => {
  e.preventDefault();
  const np = document.getElementById('newPass').value;
  const cp = document.getElementById('confirmPass').value;
  if (np !== cp) { Swal.fire({icon:'error',title:'Password Tidak Cocok',text:'Ulangi password dengan benar.',confirmButtonColor:'#6C3CE1'}); return; }
  const fd = new FormData(e.target);
  fd.append('action','change_password');
  const resp = await fetch('ajax_settings.php',{method:'POST',body:new URLSearchParams(fd)});
  const data = await resp.json();
  Swal.fire({icon:data.success?'success':'error',title:data.success?'Password Berhasil Diubah!':data.message,timer:1800,showConfirmButton:false}).then(()=>{if(data.success)e.target.reset();});
});

async function clearLogs() {
  const r = await Swal.fire({title:'Hapus Semua Log?',text:'Log aktivitas akan dihapus permanen.',icon:'warning',showCancelButton:true,confirmButtonText:'Ya, Hapus',cancelButtonText:'Batal',confirmButtonColor:'#EF4444',cancelButtonColor:'#374151'});
  if (!r.isConfirmed) return;
  const resp = await fetch('ajax_settings.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=clear_logs'});
  const data = await resp.json();
  Swal.fire({icon:data.success?'success':'error',title:data.success?'Log Dihapus!':'Gagal',timer:1500,showConfirmButton:false});
}

async function clearNotifs() {
  const r = await Swal.fire({title:'Hapus Semua Notifikasi?',icon:'warning',showCancelButton:true,confirmButtonText:'Ya, Hapus',cancelButtonText:'Batal',confirmButtonColor:'#EF4444',cancelButtonColor:'#374151'});
  if (!r.isConfirmed) return;
  const resp = await fetch('ajax_settings.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=clear_notifs'});
  const data = await resp.json();
  Swal.fire({icon:data.success?'success':'error',title:data.success?'Notifikasi Dihapus!':'Gagal',timer:1500,showConfirmButton:false});
}
</script>


</body>
</html>
