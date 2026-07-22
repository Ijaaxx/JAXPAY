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
<link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="stylesheet" href="../assets/css/theme.css?v=7">

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
