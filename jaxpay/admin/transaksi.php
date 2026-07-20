<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$filter_tipe = isset($_GET['tipe'])   ? sanitize($_GET['tipe'])   : '';
$filter_date = isset($_GET['tanggal'])? sanitize($_GET['tanggal']): '';
$search      = isset($_GET['q'])      ? sanitize($_GET['q'])       : '';
$page        = max(1, (int)($_GET['page'] ?? 1));
$per_page    = 20;
$offset      = ($page - 1) * $per_page;

$where_parts = [];
$valid_tipes = ['topup','transfer_masuk','transfer_keluar','pembayaran','qr_payment'];
if ($filter_tipe && in_array($filter_tipe, $valid_tipes)) $where_parts[] = "t.tipe='$filter_tipe'";
if ($filter_date) $where_parts[] = "DATE(t.created_at)='$filter_date'";
if ($search) $where_parts[] = "(u.nama LIKE '%$search%' OR t.kode_transaksi LIKE '%$search%')";
$where = $where_parts ? 'WHERE '.implode(' AND ',$where_parts) : '';

$total_rows = $koneksi->query("SELECT COUNT(*) as c FROM transaksi t JOIN users u ON t.user_id=u.id $where")->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $per_page);
$transaksi = $koneksi->query("SELECT t.*, u.nama, u.role FROM transaksi t JOIN users u ON t.user_id=u.id $where ORDER BY t.created_at DESC LIMIT $per_page OFFSET $offset");

$tipe_labels = ['topup'=>'Top Up','transfer_masuk'=>'Terima Transfer','transfer_keluar'=>'Kirim Transfer','pembayaran'=>'Pembayaran','qr_payment'=>'QR Payment'];
$tipe_badge  = ['topup'=>'success','transfer_masuk'=>'info','transfer_keluar'=>'warning','pembayaran'=>'primary','qr_payment'=>'muted'];

// Summary stats
$total_in  = $koneksi->query("SELECT SUM(jumlah) as s FROM transaksi WHERE tipe IN ('topup','transfer_masuk')")->fetch_assoc()['s']??0;
$total_out = $koneksi->query("SELECT SUM(jumlah) as s FROM transaksi WHERE tipe IN ('transfer_keluar','pembayaran','qr_payment')")->fetch_assoc()['s']??0;
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY Admin - Semua Transaksi</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/admin.css?v=2">
  <link rel="stylesheet" href="../assets/css/theme.css?v=4">

</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'navbar.php'; ?>

<main class="admin-main animate-in">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px">
    <div>
      <h2 style="font-size:20px;font-weight:800">Semua Transaksi</h2>
      <p style="color:var(--text-muted);font-size:13px;margin-top:3px">Total <?= number_format($total_rows) ?> transaksi tercatat</p>
    </div>
    <a href="laporan.php" class="btn btn-outline"><i class="fas fa-file-export"></i> Export Laporan</a>
  </div>

  <!-- Summary Bar -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
    <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);border-radius:14px;padding:16px;display:flex;align-items:center;gap:12px">
      <div style="width:40px;height:40px;background:rgba(16,185,129,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#10B981"><i class="fas fa-arrow-down"></i></div>
      <div><div style="font-size:11px;color:rgba(255,255,255,0.5);margin-bottom:3px">Total Dana Masuk</div><div style="font-size:16px;font-weight:800;color:#10B981">Rp <?= number_format($total_in,0,',','.') ?></div></div>
    </div>
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:14px;padding:16px;display:flex;align-items:center;gap:12px">
      <div style="width:40px;height:40px;background:rgba(239,68,68,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#EF4444"><i class="fas fa-arrow-up"></i></div>
      <div><div style="font-size:11px;color:rgba(255,255,255,0.5);margin-bottom:3px">Total Dana Keluar</div><div style="font-size:16px;font-weight:800;color:#EF4444">Rp <?= number_format($total_out,0,',','.') ?></div></div>
    </div>
    <div style="background:rgba(108,60,225,0.1);border:1px solid rgba(108,60,225,0.25);border-radius:14px;padding:16px;display:flex;align-items:center;gap:12px">
      <div style="width:40px;height:40px;background:rgba(108,60,225,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#9B72EF"><i class="fas fa-receipt"></i></div>
      <div><div style="font-size:11px;color:rgba(255,255,255,0.5);margin-bottom:3px">Total Transaksi</div><div style="font-size:16px;font-weight:800;color:#9B72EF"><?= number_format($total_rows) ?>x</div></div>
    </div>
  </div>

  <div class="admin-card">
    <div class="table-controls">
      <div class="search-input-wrap">
        <i class="fas fa-search"></i>
        <input type="text" class="table-search" id="searchInput" placeholder="Cari nama user atau kode transaksi..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <select class="table-select" id="tipeFilter">
        <option value="">Semua Tipe</option>
        <?php foreach ($tipe_labels as $k=>$v): ?>
        <option value="<?=$k?>" <?= $filter_tipe===$k?'selected':'' ?>><?=$v?></option>
        <?php endforeach; ?>
      </select>
      <input type="date" class="table-select" id="dateFilter" value="<?= htmlspecialchars($filter_date) ?>">
      <button class="btn btn-primary btn-sm" onclick="doFilter()"><i class="fas fa-filter"></i> Filter</button>
      <a href="transaksi.php" class="btn btn-outline btn-sm"><i class="fas fa-rotate-right"></i></a>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr><th>#</th><th>Kode Transaksi</th><th>Pengguna</th><th>Tipe</th><th>Jumlah</th><th>Saldo Sebelum</th><th>Saldo Setelah</th><th>Keterangan</th><th>Waktu</th></tr>
        </thead>
        <tbody>
        <?php $no=($page-1)*$per_page+1; $has_tx=false; while($tx = $transaksi->fetch_assoc()): $has_tx=true;
          $is_pos = in_array($tx['tipe'],['topup','transfer_masuk']);
          $bc = $tipe_badge[$tx['tipe']] ?? 'muted';
        ?>
        <tr>
          <td style="color:var(--text-muted);font-size:12px"><?= $no++ ?></td>
          <td><code style="font-size:11px;color:var(--accent)"><?= $tx['kode_transaksi'] ?></code></td>
          <td>
            <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($tx['nama']) ?></div>
            <div style="font-size:11px;color:var(--text-muted)"><?= ucfirst($tx['role']) ?></div>
          </td>
          <td><span class="badge badge-<?= $bc ?>"><?= $tipe_labels[$tx['tipe']]??$tx['tipe'] ?></span></td>
          <td style="font-weight:700;color:<?= $is_pos?'#10B981':'#EF4444' ?>">
            <?= $is_pos?'+':'-' ?>Rp <?= number_format($tx['jumlah'],0,',','.') ?>
          </td>
          <td style="font-size:12px">Rp <?= number_format($tx['saldo_sebelum'],0,',','.') ?></td>
          <td style="font-size:12px">Rp <?= number_format($tx['saldo_sesudah'],0,',','.') ?></td>
          <td style="font-size:12px;color:var(--text-muted);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($tx['keterangan']) ?>"><?= htmlspecialchars($tx['keterangan']??'-') ?></td>
          <td style="font-size:12px;color:var(--text-muted);white-space:nowrap"><?= date('d/m/Y H:i',strtotime($tx['created_at'])) ?></td>
        </tr>
        <?php endwhile; ?>
        <?php if (!$has_tx): ?>
        <tr>
          <td colspan="9">
            <div class="empty-state">
              <i class="fas fa-receipt"></i>
              <strong>Belum ada transaksi</strong>
              <span>Filter ini tidak memiliki data transaksi.</span>
            </div>
          </td>
        </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
      <a href="?page=<?=$page-1?>&q=<?=urlencode($search)?>&tipe=<?=urlencode($filter_tipe)?>&tanggal=<?=urlencode($filter_date)?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
      <?php endif; ?>
      <?php for($p=max(1,$page-2);$p<=min($total_pages,$page+2);$p++): ?>
      <a href="?page=<?=$p?>&q=<?=urlencode($search)?>&tipe=<?=urlencode($filter_tipe)?>&tanggal=<?=urlencode($filter_date)?>" class="page-btn <?=$p===$page?'active':''?>"><?=$p?></a>
      <?php endfor; ?>
      <?php if ($page < $total_pages): ?>
      <a href="?page=<?=$page+1?>&q=<?=urlencode($search)?>&tipe=<?=urlencode($filter_tipe)?>&tanggal=<?=urlencode($filter_date)?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

</main>
<?php include 'footer.php'; ?>
<script>
function doFilter() {
  const q    = document.getElementById('searchInput').value;
  const tipe = document.getElementById('tipeFilter').value;
  const date = document.getElementById('dateFilter').value;
  window.location.href = `transaksi.php?q=${encodeURIComponent(q)}&tipe=${encodeURIComponent(tipe)}&tanggal=${encodeURIComponent(date)}`;
}
document.getElementById('searchInput').addEventListener('keypress', e=>{ if(e.key==='Enter') doFilter(); });
let searchTimer;
document.getElementById('searchInput').addEventListener('input', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(doFilter, 500);
});
</script>


</body>
</html>
