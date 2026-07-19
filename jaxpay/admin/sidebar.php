<?php
// Admin Sidebar - included in all admin pages
$current_page = basename($_SERVER['PHP_SELF']);

// Count pending topups for badge
$pending_topup = 0;
if (isset($koneksi)) {
    $res = $koneksi->query("SELECT COUNT(*) as c FROM topup WHERE status='pending'");
    if ($res) $pending_topup = $res->fetch_assoc()['c'];
}
$admin_name = $_SESSION['admin_nama'] ?? 'Admin';
$admin_inisial = strtoupper(substr($admin_name, 0, 1));
?>
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-brand">
    <img src="../assets/img/logo.png" alt="JAXPAY" class="brand-logo">
    <div>
      <div class="brand-name">JAXPAY</div>
      <div class="brand-sub">Admin Dashboard</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section">
      <div class="sidebar-section-label">Utama</div>
      <a href="dashboard.php" class="nav-link <?= $current_page==='dashboard.php'?'active':'' ?>">
        <div class="nav-icon"><i class="fas fa-chart-pie"></i></div>
        <span class="nav-text">Dashboard</span>
      </a>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-section-label">Manajemen</div>
      <a href="users.php" class="nav-link <?= $current_page==='users.php'?'active':'' ?>">
        <div class="nav-icon"><i class="fas fa-users"></i></div>
        <span class="nav-text">Kelola User</span>
      </a>
      <a href="merchant.php" class="nav-link <?= $current_page==='merchant.php'?'active':'' ?>">
        <div class="nav-icon"><i class="fas fa-store"></i></div>
        <span class="nav-text">Merchant</span>
      </a>
      <a href="topup.php" class="nav-link <?= $current_page==='topup.php'?'active':'' ?>">
        <div class="nav-icon"><i class="fas fa-wallet"></i></div>
        <span class="nav-text">Konfirmasi Top Up</span>
        <?php if ($pending_topup > 0): ?>
        <span class="nav-badge"><?= $pending_topup ?></span>
        <?php endif; ?>
      </a>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-section-label">Laporan</div>
      <a href="transaksi.php" class="nav-link <?= $current_page==='transaksi.php'?'active':'' ?>">
        <div class="nav-icon"><i class="fas fa-receipt"></i></div>
        <span class="nav-text">Semua Transaksi</span>
      </a>
      <a href="laporan.php" class="nav-link <?= $current_page==='laporan.php'?'active':'' ?>">
        <div class="nav-icon"><i class="fas fa-chart-bar"></i></div>
        <span class="nav-text">Laporan</span>
      </a>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-section-label">Sistem</div>
      <a href="settings.php" class="nav-link <?= $current_page==='settings.php'?'active':'' ?>">
        <div class="nav-icon"><i class="fas fa-gear"></i></div>
        <span class="nav-text">Pengaturan</span>
      </a>
      <a href="logout.php" class="nav-link" style="color:#ef4444">
        <div class="nav-icon" style="color:#ef4444"><i class="fas fa-right-from-bracket"></i></div>
        <span class="nav-text">Keluar</span>
      </a>
    </div>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-admin-card">
      <div class="sidebar-admin-avatar"><?= $admin_inisial ?></div>
      <div class="sidebar-admin-info">
        <h5><?= htmlspecialchars($admin_name) ?></h5>
        <p>Super Admin</p>
      </div>
    </div>
  </div>
</aside>
