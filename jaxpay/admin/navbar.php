<?php
// Admin Top Navbar
$page_titles = [
    'dashboard.php'  => ['Dashboard', 'Ringkasan sistem JAXPAY'],
    'users.php'      => ['Kelola User', 'Manajemen pengguna'],
    'merchant.php'   => ['Merchant', 'Manajemen merchant'],
    'topup.php'      => ['Konfirmasi Top Up', 'Verifikasi pengajuan top up'],
    'transaksi.php'  => ['Semua Transaksi', 'Riwayat transaksi sistem'],
    'laporan.php'    => ['Laporan', 'Laporan & statistik'],
    'settings.php'   => ['Pengaturan', 'Konfigurasi sistem'],
];
$current_page = basename($_SERVER['PHP_SELF']);
$title_data = $page_titles[$current_page] ?? ['Admin', 'JAXPAY Admin Panel'];

$pending_topup = 0;
if (isset($koneksi)) {
    $res = $koneksi->query("SELECT COUNT(*) as c FROM topup WHERE status='pending'");
    if ($res) $pending_topup = $res->fetch_assoc()['c'];
}
?>
<header class="admin-topbar">
  <div class="topbar-left">
    <button class="topbar-btn" id="sidebarToggle" onclick="toggleSidebar()" style="display:none">
      <i class="fas fa-bars"></i>
    </button>
    <img src="../assets/img/Logo.png" alt="JAXPAY" class="topbar-logo" />
    <div>
      <div class="topbar-title"><?= $title_data[0] ?></div>
      <div class="topbar-breadcrumb">JAXPAY › <?= $title_data[0] ?></div>
    </div>
  </div>
  <div class="topbar-right">
    <div style="font-size:12px;color:var(--admin-muted,rgba(232,232,240,0.5));text-align:right;display:none" id="dateDisplay">
      <div id="clockDisplay" style="font-size:15px;font-weight:700;color:var(--admin-text,#E8E8F0)"></div>
      <div><?= date('l, d F Y') ?></div>
    </div>
    <!-- Theme Toggle Removed -->
    <a href="topup.php" class="topbar-btn" title="Konfirmasi Top Up">
      <i class="fas fa-wallet"></i>
      <?php if ($pending_topup > 0): ?>
      <div class="badge"><?= $pending_topup ?></div>
      <?php endif; ?>
    </a>
    <a href="logout.php" class="topbar-btn" title="Keluar">
      <i class="fas fa-right-from-bracket"></i>
    </a>
  </div>
</header>

<script>


function toggleSidebar() {
  document.getElementById('adminSidebar').classList.toggle('open');
}

// Clock
function updateClock() {
  const now = new Date();
  document.getElementById('clockDisplay').textContent = now.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
if (document.getElementById('clockDisplay')) {
  document.getElementById('dateDisplay').style.display = 'block';
  updateClock();
  setInterval(updateClock, 1000);
}

// Responsive sidebar toggle
if (window.innerWidth <= 900) {
  document.getElementById('sidebarToggle').style.display = 'flex';
}
window.addEventListener('resize', () => {
  document.getElementById('sidebarToggle').style.display = window.innerWidth <= 900 ? 'flex' : 'none';
});
</script>
