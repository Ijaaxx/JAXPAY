<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

// ── Stats ──
$total_users     = $koneksi->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_merchant  = $koneksi->query("SELECT COUNT(*) as c FROM merchant WHERE is_active=1")->fetch_assoc()['c'];
$total_transaksi = $koneksi->query("SELECT COUNT(*) as c FROM transaksi WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
$pending_topup   = $koneksi->query("SELECT COUNT(*) as c FROM topup WHERE status='pending'")->fetch_assoc()['c'];
$total_saldo_res = $koneksi->query("SELECT SUM(saldo) as s FROM users");
$total_saldo     = $total_saldo_res->fetch_assoc()['s'] ?? 0;
$omzet_hari_ini  = $koneksi->query("SELECT SUM(jumlah) as s FROM transaksi WHERE DATE(created_at)=CURDATE() AND tipe IN ('pembayaran','qr_payment')")->fetch_assoc()['s'] ?? 0;

// ── Chart Data: 7 days transactions ──
$chart_labels = []; $chart_data = []; $chart_topup = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d M', strtotime($date));
    $tx = $koneksi->query("SELECT SUM(jumlah) as s FROM transaksi WHERE DATE(created_at)='$date' AND tipe IN ('pembayaran','qr_payment')")->fetch_assoc()['s'] ?? 0;
    $tp = $koneksi->query("SELECT SUM(jumlah) as s FROM topup WHERE DATE(created_at)='$date' AND status='approved'")->fetch_assoc()['s'] ?? 0;
    $chart_data[] = (float)$tx;
    $chart_topup[] = (float)$tp;
}

// ── Role Distribution ──
$role_data = [];
$roles = ['student','teacher','parent','merchant'];
foreach ($roles as $r) {
    $role_data[] = (int)$koneksi->query("SELECT COUNT(*) as c FROM users WHERE role='$r'")->fetch_assoc()['c'];
}

// ── Recent Transactions ──
$recent_tx = $koneksi->query("SELECT t.*, u.nama FROM transaksi t JOIN users u ON t.user_id=u.id ORDER BY t.created_at DESC LIMIT 8");

// ── Activity Logs ──
$activity = $koneksi->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 8");

// ── Recent Top Up Pending ──
$pending_list = $koneksi->query("SELECT tp.*, u.nama, u.email FROM topup tp JOIN users u ON tp.user_id=u.id WHERE tp.status='pending' ORDER BY tp.created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY Admin - Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../assets/css/admin.css">
  <link rel="stylesheet" href="../assets/css/theme.css">
  <script src="../assets/js/theme.js"></script>
  <style>
    html[data-theme="light"] { color-scheme: light; }
    body { background: #f6f8fc !important; color: #0f172a !important; }
    .admin-sidebar, .admin-topbar, .admin-card, .stat-card, .chart-container, .table-wrapper, table, .activity-item, .admin-main {
      background: #ffffff !important;
      color: #0f172a !important;
      border-color: rgba(15,23,42,0.12) !important;
      box-shadow: 0 16px 42px rgba(15,23,42,0.08) !important;
    }
    .admin-sidebar { background: #ffffff !important; }
    .admin-topbar { background: #ffffff !important; }
    .topbar-btn, .theme-toggle-admin, .badge, .card, .tx-item, .notif-item, .user-card, .merchant-card, .info-item { background: rgba(255,255,255,0.88) !important; color: #0f172a !important; border-color: rgba(15,23,42,0.12) !important; }
    th, td { color: #1f2937 !important; }
    thead tr { background: rgba(226,232,240,0.6) !important; }
    tbody tr { background: rgba(248,250,252,0.8) !important; }
    tbody tr:nth-child(even) { background: rgba(241,245,249,0.9) !important; }
    tbody tr:hover { background: rgba(226,232,240,0.8) !important; }
    .page-title, .topbar-title, .sidebar-brand .brand-name, .sidebar-brand .brand-sub, .nav-link, .nav-link.active, .nav-link:hover, .admin-card-header h3, .stat-label, .stat-change, .activity-text, .activity-time {
      color: #0f172a !important;
    }
    .badge { color: #0f172a !important; background: rgba(59,130,246,0.12) !important; }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>
<?php include 'navbar.php'; ?>

<main class="admin-main animate-in">

  <!-- ── STAT CARDS ── -->
  <div class="stats-grid">
    <div class="stat-card purple">
      <div class="stat-icon purple"><i class="fas fa-users"></i></div>
      <div class="stat-value"><?= number_format($total_users) ?></div>
      <div class="stat-label">Total Pengguna</div>
      <div class="stat-change up"><i class="fas fa-arrow-up"></i> Aktif di sistem</div>
    </div>
    <div class="stat-card cyan">
      <div class="stat-icon cyan"><i class="fas fa-store"></i></div>
      <div class="stat-value"><?= number_format($total_merchant) ?></div>
      <div class="stat-label">Merchant Aktif</div>
      <div class="stat-change up"><i class="fas fa-check-circle"></i> Terverifikasi</div>
    </div>
    <div class="stat-card green">
      <div class="stat-icon green"><i class="fas fa-receipt"></i></div>
      <div class="stat-value"><?= number_format($total_transaksi) ?></div>
      <div class="stat-label">Transaksi Hari Ini</div>
      <div class="stat-change up"><i class="fas fa-calendar-day"></i> <?= date('d M Y') ?></div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
      <div class="stat-value"><?= number_format($pending_topup) ?></div>
      <div class="stat-label">Top Up Pending</div>
      <?php if ($pending_topup > 0): ?>
      <div class="stat-change down"><i class="fas fa-exclamation-triangle"></i> Butuh konfirmasi!</div>
      <?php else: ?>
      <div class="stat-change up"><i class="fas fa-check"></i> Semua terproses</div>
      <?php endif; ?>
    </div>
    <div class="stat-card indigo">
      <div class="stat-icon indigo"><i class="fas fa-wallet"></i></div>
      <div class="stat-value" style="font-size:18px">Rp <?= number_format($total_saldo,0,',','.') ?></div>
      <div class="stat-label">Total Saldo Sistem</div>
      <div class="stat-change up"><i class="fas fa-coins"></i> Saldo beredar</div>
    </div>
    <div class="stat-card green">
      <div class="stat-icon green"><i class="fas fa-bolt"></i></div>
      <div class="stat-value" style="font-size:18px">Rp <?= number_format($omzet_hari_ini,0,',','.') ?></div>
      <div class="stat-label">Omzet Hari Ini</div>
      <div class="stat-change up"><i class="fas fa-chart-line"></i> Pembayaran + QR</div>
    </div>
  </div>

  <!-- ── CHARTS ── -->
  <div class="charts-grid">
    <div class="admin-card">
      <div class="admin-card-header">
        <h3><i class="fas fa-chart-line" style="color:var(--primary-light)"></i> Grafik Transaksi 7 Hari</h3>
        <span class="badge badge-primary">Real-time</span>
      </div>
      <div class="admin-card-body padded">
        <div class="chart-container"><canvas id="txChart"></canvas></div>
      </div>
    </div>
    <div class="admin-card">
      <div class="admin-card-header">
        <h3><i class="fas fa-chart-pie" style="color:var(--accent)"></i> Distribusi User</h3>
      </div>
      <div class="admin-card-body padded">
        <div class="chart-container"><canvas id="roleChart"></canvas></div>
      </div>
    </div>
  </div>

  <!-- ── BOTTOM ROW ── -->
  <div style="display:grid;grid-template-columns:2fr 1fr;gap:18px">

    <!-- Recent Transactions -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h3><i class="fas fa-receipt" style="color:var(--success)"></i> Transaksi Terbaru</h3>
        <a href="transaksi.php" class="btn btn-outline btn-sm">Lihat Semua</a>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>User</th><th>Tipe</th><th>Jumlah</th><th>Waktu</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $tipe_labels = ['topup'=>'Top Up','transfer_masuk'=>'Terima','transfer_keluar'=>'Kirim','pembayaran'=>'Bayar','qr_payment'=>'QR Pay'];
          while ($tx = $recent_tx->fetch_assoc()):
            $is_pos = in_array($tx['tipe'], ['topup','transfer_masuk']);
          ?>
          <tr>
            <td><?= htmlspecialchars($tx['nama']) ?></td>
            <td><span class="badge badge-<?= $is_pos?'success':'warning' ?>"><?= $tipe_labels[$tx['tipe']]??$tx['tipe'] ?></span></td>
            <td style="font-weight:700;color:<?= $is_pos?'#10B981':'#EF4444' ?>">
              <?= $is_pos?'+':'-' ?>Rp <?= number_format($tx['jumlah'],0,',','.') ?>
            </td>
            <td style="color:var(--text-muted);font-size:12px"><?= date('d/m H:i',strtotime($tx['created_at'])) ?></td>
            <td><span class="badge badge-success">Success</span></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Activity Log + Pending Topup -->
    <div style="display:flex;flex-direction:column;gap:18px">

      <!-- Pending Top Up -->
      <?php if ($pending_topup > 0): ?>
      <div class="admin-card" style="border-color:rgba(245,158,11,0.3)">
        <div class="admin-card-header">
          <h3><i class="fas fa-exclamation-circle" style="color:var(--warning)"></i> Perlu Konfirmasi</h3>
          <a href="topup.php" class="btn btn-warning btn-sm">Proses Semua</a>
        </div>
        <div class="admin-card-body padded" style="padding-top:0">
          <?php while ($tp = $pending_list->fetch_assoc()): ?>
          <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)">
            <div style="width:38px;height:38px;border-radius:10px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center;color:#F59E0B;flex-shrink:0">
              <i class="fas fa-wallet"></i>
            </div>
            <div style="flex:1;min-width:0">
              <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($tp['nama']) ?></div>
              <div style="font-size:11px;color:var(--text-muted)">Rp <?= number_format($tp['jumlah'],0,',','.') ?> · <?= $tp['metode_bayar'] ?></div>
            </div>
            <a href="topup.php" class="btn btn-warning btn-sm btn-icon"><i class="fas fa-arrow-right"></i></a>
          </div>
          <?php endwhile; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Activity Log -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h3><i class="fas fa-clock-rotate-left" style="color:var(--info)"></i> Log Aktivitas</h3>
        </div>
        <div class="admin-card-body padded">
          <?php while ($log = $activity->fetch_assoc()): ?>
          <div class="activity-item">
            <div class="activity-dot" style="background:<?= $log['user_type']==='admin'?'var(--primary-light)':'var(--accent)' ?>"></div>
            <div>
              <div class="activity-text">
                <strong><?= $log['user_type']==='admin'?'Admin':'User' ?></strong>: <?= htmlspecialchars($log['aksi']) ?>
              </div>
              <?php if ($log['detail']): ?>
              <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars(substr($log['detail'],0,60)) ?>...</div>
              <?php endif; ?>
              <div class="activity-time"><?= date('d M Y H:i',strtotime($log['created_at'])) ?></div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
      </div>

    </div>
  </div>

</main>
<?php include 'footer.php'; ?>

<script>
// Transaction Chart
const txCtx = document.getElementById('txChart').getContext('2d');
new Chart(txCtx, {
  type: 'line',
  data: {
    labels: <?= json_encode($chart_labels) ?>,
    datasets: [
      {
        label: 'Pembayaran (Rp)',
        data: <?= json_encode($chart_data) ?>,
        borderColor: '#6C3CE1', backgroundColor: 'rgba(108,60,225,0.1)',
        tension: 0.4, fill: true, pointRadius: 5, pointBackgroundColor: '#6C3CE1', pointBorderColor: '#fff', pointBorderWidth: 2
      },
      {
        label: 'Top Up Approved (Rp)',
        data: <?= json_encode($chart_topup) ?>,
        borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,0.08)',
        tension: 0.4, fill: true, pointRadius: 5, pointBackgroundColor: '#10B981', pointBorderColor: '#fff', pointBorderWidth: 2
      }
    ]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: {
      legend: { labels: { color: '#334155', font: { size: 12 } } },
      tooltip: { titleColor: '#0f172a', bodyColor: '#334155', backgroundColor: '#fff', borderColor: '#e2e8f0', borderWidth: 1 }
    },
    scales: {
      x: {
        ticks: { color: '#475569', font:{size:11} },
        grid: { color: 'rgba(15,23,42,0.06)' }
      },
      y: {
        ticks: { color: '#475569', font:{size:11}, callback: v => 'Rp '+v.toLocaleString('id-ID') },
        grid: { color: 'rgba(15,23,42,0.06)' }
      }
    }
  }
});

// Role Chart
const roleCtx = document.getElementById('roleChart').getContext('2d');
new Chart(roleCtx, {
  type: 'doughnut',
  data: {
    labels: ['Student', 'Teacher', 'Parent', 'Merchant'],
    datasets: [{
      data: <?= json_encode($role_data) ?>,
      backgroundColor: ['rgba(108,60,225,0.8)','rgba(0,212,255,0.8)','rgba(16,185,129,0.8)','rgba(245,158,11,0.8)'],
      borderColor: '#16162A', borderWidth: 3, hoverOffset: 8
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false, cutout: '65%',
    plugins: { legend: { position: 'bottom', labels: { color: '#334155', font:{size:12}, padding: 14 } },
      tooltip: { titleColor: '#0f172a', bodyColor: '#334155', backgroundColor: '#fff', borderColor: '#e2e8f0', borderWidth: 1 }
    }
  }
});

if (window.JaxpayCharts) window.JaxpayCharts.applyAllCharts();
</script>


</body>
</html>
