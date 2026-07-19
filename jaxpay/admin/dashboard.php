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
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY Admin - Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../assets/css/admin.css?v=2">
  <link rel="stylesheet" href="../assets/css/theme.css?v=4">
  
</head>
<body><?php include 'sidebar.php'; ?>
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
      <div class="admin-card-body" style="padding: 0;">
        <div class="chart-wrapper" style="position: relative; height: 380px; width: 100%; padding: 20px;">
          <div class="chart-loading" data-loader="txChart">
            <div class="spinner"></div>
            <div>Memuat grafik transaksi...</div>
          </div>
          <canvas id="txChart"></canvas>
        </div>
      </div>
    </div>
    <div class="admin-card">
      <div class="admin-card-header">
        <h3><i class="fas fa-chart-pie" style="color:var(--accent)"></i> Distribusi User</h3>
      </div>
      <div class="admin-card-body" style="padding: 0;">
        <div class="chart-wrapper" style="position: relative; height: 380px; width: 100%; padding: 20px;">
          <div class="chart-loading" data-loader="roleChart">
            <div class="spinner"></div>
            <div>Memuat distribusi user...</div>
          </div>
          <canvas id="roleChart"></canvas>
        </div>
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
              <th>Kode</th><th>User</th><th>Tipe</th><th>Jumlah</th><th>Waktu</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $tipe_labels = ['topup'=>'Top Up','transfer_masuk'=>'Terima','transfer_keluar'=>'Kirim','pembayaran'=>'Bayar','qr_payment'=>'QR Pay'];
          while ($tx = $recent_tx->fetch_assoc()):
            $is_pos = in_array($tx['tipe'], ['topup','transfer_masuk']);
            $tx_status = strtolower($tx['status'] ?? 'success');
            $status_class = in_array($tx_status, ['success','completed','approved','paid']) ? 'success' : (in_array($tx_status, ['pending','waiting']) ? 'warning' : 'danger');
            $status_label = $tx['status'] ?? ($is_pos ? 'Success' : 'Completed');
          ?>
          <tr>
            <td><code style="font-size:11px;color:var(--accent)"><?= htmlspecialchars($tx['kode_transaksi']) ?></code></td>
            <td><?= htmlspecialchars($tx['nama']) ?></td>
            <td><span class="badge badge-<?= $is_pos ? 'success' : 'warning' ?>"><?= htmlspecialchars($tipe_labels[$tx['tipe']] ?? $tx['tipe']) ?></span></td>
            <td style="font-weight:700;color:<?= $is_pos ? '#10B981' : '#EF4444' ?>">
              <?= $is_pos ? '+' : '-' ?>Rp <?= number_format($tx['jumlah'], 0, ',', '.') ?>
            </td>
            <td style="color:var(--text-muted);font-size:12px"><?= date('d/m H:i', strtotime($tx['created_at'])) ?></td>
            <td><span class="badge badge-<?= $status_class ?>"><?= htmlspecialchars(ucfirst($status_label)) ?></span></td>
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
function getThemeColors() {
  return {
    txBg: '#6C3CE1',
    txAccent: '#00D4FF',
    mutedColor: '#64748b',
    surfaceBorder: 'rgba(15, 23, 42, 0.1)',
    surfaceColor: '#ffffff',
    textPrimary: '#0f172a',
    txGradientStart: 'rgba(108, 60, 225, 0.38)',
    txGradientEnd: 'rgba(108, 60, 225, 0.03)',
    txAccentGradientStart: 'rgba(0, 212, 255, 0.32)',
    txAccentGradientEnd: 'rgba(0, 212, 255, 0.04)'
  };
}

function createGradient(ctx, start, end) {
  const gradient = ctx.createLinearGradient(0, 0, 0, 380);
  gradient.addColorStop(0, start);
  gradient.addColorStop(1, end);
  return gradient;
}

function hideChartLoader(chartId) {
  const loader = document.querySelector(`.chart-loading[data-loader="${chartId}"]`);
  if (loader) loader.classList.add('is-hidden');
}

let txChartInstance = null;
let roleChartInstance = null;

function renderCharts() {
  const colors = getThemeColors();

  let txCtx = document.getElementById('txChart');
  if (txCtx && !txChartInstance) {

    const txGradient = createGradient(txCtx.getContext('2d'), colors.txGradientStart, colors.txGradientEnd);
    const txTopupGradient = createGradient(txCtx.getContext('2d'), colors.txAccentGradientStart, colors.txAccentGradientEnd);

    txChartInstance = new Chart(txCtx.getContext('2d'), {
      type: 'line',
      data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [
          {
            label: 'Pembayaran',
            data: <?= json_encode($chart_data) ?>,
            borderColor: colors.txBg,
            backgroundColor: txGradient,
            tension: 0.42,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 8,
            pointBackgroundColor: '#fff',
            pointBorderColor: colors.txBg,
            pointBorderWidth: 2,
            borderWidth: 3,
            hoverBorderWidth: 3,
            pointHoverBorderColor: colors.textPrimary,
            pointStyle: 'circle'
          },
          {
            label: 'Top Up Approved',
            data: <?= json_encode($chart_topup) ?>,
            borderColor: colors.txAccent,
            backgroundColor: txTopupGradient,
            tension: 0.42,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 8,
            pointBackgroundColor: '#fff',
            pointBorderColor: colors.txAccent,
            pointBorderWidth: 2,
            borderWidth: 3,
            hoverBorderWidth: 3,
            pointStyle: 'circle'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 1000, easing: 'easeOutQuart' },
        layout: { padding: { top: 10, right: 10, bottom: 0, left: 0 } },
        interaction: { mode: 'nearest', intersect: false, axis: 'x' },
        plugins: {
          legend: {
            position: 'top',
            labels: {
              color: colors.textPrimary,
              boxWidth: 10,
              boxHeight: 10,
              padding: 20,
              usePointStyle: true,
              pointStyle: 'circle',
              font: { size: 13, weight: '600', family: "'Inter', 'Segoe UI', sans-serif" }
            }
          },
          tooltip: {
            backgroundColor: colors.surfaceColor,
            titleColor: colors.textPrimary,
            bodyColor: colors.textPrimary,
            borderColor: colors.surfaceBorder,
            borderWidth: 1,
            displayColors: false,
            padding: 14,
            cornerRadius: 14,
            callbacks: {
              label: (ctx) => ` Rp ${Number(ctx.parsed.y || 0).toLocaleString('id-ID')}`
            }
          }
        },
        scales: {
          x: {
            grid: { color: colors.surfaceBorder, drawBorder: false, display: false },
            ticks: { color: colors.mutedColor, font: { size: 12, family: "'Inter', sans-serif" }, padding: 10 },
            border: { color: colors.surfaceBorder, display: false }
          },
          y: {
            beginAtZero: true,
            grid: { color: colors.surfaceBorder, borderDash: [5, 5], drawBorder: false },
            ticks: {
              color: colors.mutedColor,
              font: { size: 12, family: "'Inter', sans-serif" },
              padding: 10,
              callback: (value) => `Rp ${Number(value).toLocaleString('id-ID')}`
            },
            border: { color: colors.surfaceBorder, display: false }
          }
        }
      }
    });
    hideChartLoader('txChart');
  }

  let roleCtx = document.getElementById('roleChart');
  if (roleCtx && !roleChartInstance) {

    
    roleChartInstance = new Chart(roleCtx.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Student', 'Teacher', 'Parent', 'Merchant'],
        datasets: [{
          data: <?= json_encode($role_data) ?>,
          backgroundColor: ['#6C3CE1', '#00D4FF', '#10B981', '#F59E0B'],
          borderColor: colors.surfaceColor,
          borderWidth: 3,
          hoverOffset: 14,
          hoverBorderColor: '#fff',
          hoverBorderWidth: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { animateRotate: true, duration: 1100, easing: 'easeOutQuart' },
        cutout: '70%',
        layout: { padding: 20 },
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: colors.textPrimary,
              font: { size: 13, weight: '600', family: "'Inter', 'Segoe UI', sans-serif" },
              padding: 20,
              usePointStyle: true,
              pointStyle: 'circle'
            }
          },
          tooltip: {
            backgroundColor: colors.surfaceColor,
            titleColor: colors.textPrimary,
            bodyColor: colors.textPrimary,
            borderColor: colors.surfaceBorder,
            borderWidth: 1,
            displayColors: true,
            usePointStyle: true,
            padding: 16,
            cornerRadius: 14,
            callbacks: {
              label: (ctx) => {
                const value = ctx.parsed.toLocaleString('id-ID');
                const total = ctx.chart.data.datasets[ctx.datasetIndex].data.reduce((sum, item) => sum + (Number(item) || 0), 0);
                const percent = total ? Math.round((ctx.parsed / total) * 100) : 0;
                return ` ${ctx.label}: ${value} (${percent}%)`;
              }
            }
          }
        },
        interaction: { mode: 'nearest', intersect: true }
      }
    });
    hideChartLoader('roleChart');
  }
}

// Initial draw
renderCharts();

// Fix for BFCache (canvas cleared on navigating back)
window.addEventListener('pageshow', (e) => {
  if (e.persisted) {
    if (txChartInstance) txChartInstance.update();
    if (roleChartInstance) roleChartInstance.update();
  }
});
</script>


</body>
</html>
