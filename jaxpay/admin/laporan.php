<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$month = isset($_GET['month']) ? sanitize($_GET['month']) : date('Y-m');
[$year, $mon] = explode('-', $month);

// Monthly stats
$total_tx_bulan  = $koneksi->query("SELECT COUNT(*) as c FROM transaksi WHERE YEAR(created_at)=$year AND MONTH(created_at)=$mon")->fetch_assoc()['c'];
$total_topup_bulan = $koneksi->query("SELECT COALESCE(SUM(jumlah),0) as s FROM topup WHERE YEAR(created_at)=$year AND MONTH(created_at)=$mon AND status='approved'")->fetch_assoc()['s'];
$total_pay_bulan = $koneksi->query("SELECT COALESCE(SUM(jumlah),0) as s FROM transaksi WHERE YEAR(created_at)=$year AND MONTH(created_at)=$mon AND tipe IN ('pembayaran','qr_payment')")->fetch_assoc()['s'];
$new_users_bulan = $koneksi->query("SELECT COUNT(*) as c FROM users WHERE YEAR(created_at)=$year AND MONTH(created_at)=$mon")->fetch_assoc()['c'];

// Daily chart data for the month
$days_in_month = date('t', mktime(0,0,0,$mon,1,$year));
$daily_labels=[]; $daily_tx=[]; $daily_topup=[];
for ($d=1; $d<=$days_in_month; $d++) {
  $date = sprintf('%04d-%02d-%02d', $year, $mon, $d);
  $daily_labels[] = $d;
  $daily_tx[]   = (float)$koneksi->query("SELECT COALESCE(SUM(jumlah),0) as s FROM transaksi WHERE DATE(created_at)='$date' AND tipe IN ('pembayaran','qr_payment')")->fetch_assoc()['s'];
  $daily_topup[]= (float)$koneksi->query("SELECT COALESCE(SUM(jumlah),0) as s FROM topup WHERE DATE(created_at)='$date' AND status='approved'")->fetch_assoc()['s'];
}

// Top merchants
$top_merchants = $koneksi->query("SELECT m.nama_toko, m.total_transaksi, m.total_omzet FROM merchant m ORDER BY m.total_omzet DESC LIMIT 5");

// Role distribution
$role_stats = [];
foreach (['student','teacher','parent','merchant'] as $r) {
  $role_stats[$r] = $koneksi->query("SELECT COUNT(*) as c FROM users WHERE role='$r'")->fetch_assoc()['c'];
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY Admin - Laporan</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../assets/css/admin.css?v=6">
  <link rel="stylesheet" href="../assets/css/theme.css?v=6">

</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'navbar.php'; ?>

<main class="admin-main animate-in">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px">
    <div>
      <h2 style="font-size:20px;font-weight:800">Laporan & Statistik</h2>
      <p style="color:var(--text-muted);font-size:13px;margin-top:3px">Laporan bulanan sistem JAXPAY</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
      <input type="month" id="monthPicker" value="<?= $month ?>" class="table-select" onchange="location.href='laporan.php?month='+this.value">
      <button class="btn btn-primary" onclick="printReport()"><i class="fas fa-print"></i> Cetak</button>
    </div>
  </div>

  <!-- Summary Cards -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px" id="printArea">
    <div class="stat-card purple">
      <div class="stat-icon purple"><i class="fas fa-receipt"></i></div>
      <div class="stat-value"><?= number_format($total_tx_bulan) ?></div>
      <div class="stat-label">Total Transaksi Bulan Ini</div>
    </div>
    <div class="stat-card green">
      <div class="stat-icon green"><i class="fas fa-wallet"></i></div>
      <div class="stat-value" style="font-size:18px">Rp <?= number_format($total_topup_bulan,0,',','.') ?></div>
      <div class="stat-label">Total Top Up Approved</div>
    </div>
    <div class="stat-card cyan">
      <div class="stat-icon cyan"><i class="fas fa-bolt"></i></div>
      <div class="stat-value" style="font-size:18px">Rp <?= number_format($total_pay_bulan,0,',','.') ?></div>
      <div class="stat-label">Total Pembayaran Merchant</div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange"><i class="fas fa-user-plus"></i></div>
      <div class="stat-value"><?= number_format($new_users_bulan) ?></div>
      <div class="stat-label">User Baru Bulan Ini</div>
    </div>
  </div>

  <!-- Charts -->
  <div class="admin-card" style="margin-bottom:22px">
    <div class="admin-card-header">
      <h3><i class="fas fa-chart-area" style="color:var(--primary-light)"></i> Grafik Harian — <?= date('F Y', mktime(0,0,0,$mon,1,$year)) ?></h3>
    </div>
    <div class="admin-card-body padded">
      <div style="position:relative;height:260px;width:100%"><canvas id="dailyChart"></canvas></div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:3fr 2fr;gap:18px">

    <!-- Top Merchants -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h3><i class="fas fa-trophy" style="color:var(--warning)"></i> Top Merchant</h3>
      </div>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>Rank</th><th>Toko</th><th>Transaksi</th><th>Omzet</th></tr></thead>
          <tbody>
          <?php $rank=1; while($m = $top_merchants->fetch_assoc()): ?>
          <tr>
            <td>
              <?php if ($rank===1): ?><span style="font-size:18px">🥇</span>
              <?php elseif ($rank===2): ?><span style="font-size:18px">🥈</span>
              <?php elseif ($rank===3): ?><span style="font-size:18px">🥉</span>
              <?php else: ?><span style="color:var(--text-muted);font-weight:700">#<?=$rank?></span><?php endif; ?>
              <?php $rank++; ?>
            </td>
            <td style="font-weight:600"><?= htmlspecialchars($m['nama_toko']) ?></td>
            <td><span class="badge badge-primary"><?= number_format($m['total_transaksi'],0,',','.') ?>x</span></td>
            <td style="font-weight:700;color:var(--success)">Rp <?= number_format($m['total_omzet'],0,',','.') ?></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Role Breakdown -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h3><i class="fas fa-users" style="color:var(--accent)"></i> Distribusi Pengguna</h3>
      </div>
      <div class="admin-card-body padded">
        <?php
        $total_u = array_sum($role_stats);
        $role_colors = ['student'=>['#6C3CE1','rgba(108,60,225,0.15)'],'teacher'=>['#00D4FF','rgba(0,212,255,0.15)'],'parent'=>['#10B981','rgba(16,185,129,0.15)'],'merchant'=>['#F59E0B','rgba(245,158,11,0.15)']];
        foreach ($role_stats as $role => $cnt):
          $pct = $total_u > 0 ? round($cnt/$total_u*100) : 0;
          [$col,$bg] = $role_colors[$role];
        ?>
        <div style="margin-bottom:16px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <span style="font-size:13px;font-weight:600;text-transform:capitalize"><?= $role ?></span>
            <span style="font-size:12px;color:var(--text-muted)"><?= $cnt ?> (<?= $pct ?>%)</span>
          </div>
          <div style="background:rgba(255,255,255,0.08);border-radius:6px;height:8px;overflow:hidden">
            <div style="height:100%;width:<?= $pct ?>%;background:<?= $col ?>;border-radius:6px;transition:width 0.8s ease"></div>
          </div>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);font-size:13px;color:var(--text-muted);text-align:center">
          Total <strong style="color:var(--text)"><?= $total_u ?></strong> pengguna terdaftar
        </div>
      </div>
    </div>
  </div>

</main>
<?php include 'footer.php'; ?>

<script>
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
new Chart(dailyCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($daily_labels) ?>,
    datasets: [
      { label: 'Pembayaran (Rp)', data: <?= json_encode($daily_tx) ?>, backgroundColor: 'rgba(108,60,225,0.6)', borderColor: '#6C3CE1', borderWidth: 2, borderRadius: 6 },
      { label: 'Top Up (Rp)', data: <?= json_encode($daily_topup) ?>, backgroundColor: 'rgba(16,185,129,0.5)', borderColor: '#10B981', borderWidth: 2, borderRadius: 6 }
    ]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { labels: { color: '#0f172a', font:{size:12} } } },
    scales: {
      x: { ticks: { color: 'rgba(15,23,42,0.6)' }, grid: { color: 'rgba(15,23,42,0.08)' } },
      y: { ticks: { color: 'rgba(15,23,42,0.6)', callback: v => 'Rp ' + (Number(v) || 0).toLocaleString('id-ID') }, grid: { color: 'rgba(15,23,42,0.08)' } }
    }
  }
});

if (window.JaxpayCharts) window.JaxpayCharts.applyAllCharts();

function printReport() { window.print(); }
</script>


</body>
</html>
