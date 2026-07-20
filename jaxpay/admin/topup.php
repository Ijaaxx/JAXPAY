<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'pending';
if (!in_array($filter, ['pending','approved','rejected'])) $filter = 'pending';

$topups = $koneksi->query("SELECT tp.*, u.nama, u.email, u.member_id FROM topup tp JOIN users u ON tp.user_id=u.id WHERE tp.status='$filter' ORDER BY tp.created_at DESC");
$counts = [];
foreach (['pending','approved','rejected'] as $s) {
  $counts[$s] = $koneksi->query("SELECT COUNT(*) as c FROM topup WHERE status='$s'")->fetch_assoc()['c'];
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY Admin - Konfirmasi Top Up</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/admin.css?v=7">
<style>
.topup-tabs { display:flex; gap:8px; margin-bottom:20px; }
.topup-tab {
  padding:9px 22px; border-radius:10px; border:1px solid var(--border);
  background:transparent; color:var(--text-muted); font-size:13px; font-weight:600;
  cursor:pointer; transition:all 0.2s; font-family:inherit;
}
.topup-tab.active { background:var(--primary); color:#fff; border-color:var(--primary); }
.proof-thumb { width:60px; height:60px; border-radius:8px; object-fit:cover; cursor:pointer; border:1px solid var(--border); transition:all 0.2s; }
.proof-thumb:hover { transform:scale(1.08); border-color:var(--primary-light); }
.no-proof { width:60px; height:60px; border-radius:8px; background:var(--surface-3); display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:18px; }
</style>
  <link rel="stylesheet" href="../assets/css/theme.css?v=7">

</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'navbar.php'; ?>

<main class="admin-main animate-in">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px">
    <div>
      <h2 style="font-size:20px;font-weight:800">Konfirmasi Top Up</h2>
      <p style="color:var(--text-muted);font-size:13px;margin-top:3px">Verifikasi & setujui pengajuan top up pengguna</p>
    </div>
    <?php if ($counts['pending'] > 0): ?>
    <div style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);border-radius:12px;padding:10px 18px;display:flex;align-items:center;gap:8px;color:#F59E0B;font-weight:700">
      <i class="fas fa-exclamation-triangle"></i> <?= $counts['pending'] ?> pengajuan menunggu konfirmasi
    </div>
    <?php endif; ?>
  </div>

  <!-- Tabs -->
  <div class="topup-tabs">
    <button class="topup-tab <?= $filter==='pending'?'active':'' ?>" onclick="location.href='topup.php?status=pending'">
      <i class="fas fa-clock"></i> Pending <span style="background:rgba(245,158,11,0.3);padding:1px 8px;border-radius:10px;margin-left:6px"><?= $counts['pending'] ?></span>
    </button>
    <button class="topup-tab <?= $filter==='approved'?'active':'' ?>" onclick="location.href='topup.php?status=approved'">
      <i class="fas fa-check-circle"></i> Disetujui <span style="background:rgba(16,185,129,0.2);padding:1px 8px;border-radius:10px;margin-left:6px"><?= $counts['approved'] ?></span>
    </button>
    <button class="topup-tab <?= $filter==='rejected'?'active':'' ?>" onclick="location.href='topup.php?status=rejected'">
      <i class="fas fa-times-circle"></i> Ditolak <span style="background:rgba(239,68,68,0.2);padding:1px 8px;border-radius:10px;margin-left:6px"><?= $counts['rejected'] ?></span>
    </button>
  </div>

  <div class="admin-card">
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Kode</th><th>Pengguna</th><th>Nominal</th><th>Metode</th><th>Bukti</th><th>Catatan</th><th>Tanggal</th><th>Status</th>
            <?php if ($filter==='pending'): ?><th>Aksi</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php $count=0; while($tp = $topups->fetch_assoc()): $count++; ?>
        <tr>
          <td><code style="font-size:11px;color:var(--accent)"><?= $tp['kode_topup'] ?></code></td>
          <td>
            <div>
              <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($tp['nama']) ?></div>
              <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($tp['email']) ?></div>
              <code style="font-size:10px;color:var(--primary-light)"><?= $tp['member_id'] ?></code>
            </div>
          </td>
          <td style="font-weight:700;font-size:15px;color:var(--success)">Rp <?= number_format($tp['jumlah'],0,',','.') ?></td>
          <td style="font-size:12px"><?= htmlspecialchars($tp['metode_bayar']) ?></td>
          <td>
            <?php if ($tp['bukti_bayar']): ?>
            <img src="../assets/uploads/topup/<?= htmlspecialchars($tp['bukti_bayar']) ?>" class="proof-thumb"
              onclick="viewProof('../assets/uploads/topup/<?= htmlspecialchars($tp['bukti_bayar']) ?>')" alt="Bukti">
            <?php else: ?>
            <div class="no-proof"><i class="fas fa-image"></i></div>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($tp['catatan']??'-') ?></td>
          <td style="font-size:12px;color:var(--text-muted)"><?= date('d M Y H:i', strtotime($tp['created_at'])) ?></td>
          <td>
            <span class="badge badge-<?= $tp['status']==='pending'?'warning':($tp['status']==='approved'?'success':'danger') ?>">
              <?= ucfirst($tp['status']) ?>
            </span>
          </td>
          <?php if ($filter==='pending'): ?>
          <td>
            <div style="display:flex;gap:6px">
              <button class="btn btn-success btn-sm" onclick="confirmTopup(<?= $tp['id'] ?>, '<?= htmlspecialchars($tp['nama']) ?>', <?= $tp['jumlah'] ?>)">
                <i class="fas fa-check"></i> Setujui
              </button>
              <button class="btn btn-danger btn-sm" onclick="rejectTopup(<?= $tp['id'] ?>, '<?= htmlspecialchars($tp['nama']) ?>')">
                <i class="fas fa-times"></i> Tolak
              </button>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endwhile;
        if ($count===0): ?>
        <tr>
          <td colspan="9">
            <div class="empty-state">
              <i class="fas fa-inbox"></i>
              <strong>Tidak ada data top up <?= $filter ?></strong>
              <span>Data akan muncul ketika pengguna membuat pengajuan baru.</span>
            </div>
          </td>
        </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>
<?php include 'footer.php'; ?>

<!-- Proof Image Modal -->
<div class="modal-backdrop" id="proofModal">
  <div style="background:#000;border-radius:16px;padding:8px;max-width:90vw;max-height:90vh;position:relative">
    <button onclick="closeModal('proofModal')" style="position:absolute;top:-14px;right:-14px;width:32px;height:32px;border-radius:50%;background:#EF4444;border:none;color:#fff;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center">
      <i class="fas fa-times"></i>
    </button>
    <img id="proofImage" src="" style="max-width:80vw;max-height:80vh;border-radius:12px;display:block">
  </div>
</div>

<script>
function viewProof(src) {
  document.getElementById('proofImage').src = src;
  document.getElementById('proofModal').classList.add('open');
}
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-backdrop').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));

async function confirmTopup(id, nama, jumlah) {
  const r = await Swal.fire({
    title:'✅ Setujui Top Up?',
    html:`Setujui top up <strong>Rp ${jumlah.toLocaleString('id-ID')}</strong> dari <strong>${nama}</strong>?<br><small style="color:rgba(255,255,255,0.5)">Saldo user akan otomatis bertambah.</small>`,
    icon:'question', showCancelButton:true,
    confirmButtonText:'<i class="fas fa-check"></i> Ya, Setujui',
    cancelButtonText:'Batal',
     confirmButtonColor:'#10B981', cancelButtonColor:'#374151'
  });
  if (!r.isConfirmed) return;
  Swal.fire({title:'Memproses...',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
  const resp = await fetch('../proses/admin_confirm.php', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`topup_id=${id}&action=approve&note=`});
  const data = await resp.json();
  if (data.success) {
    Swal.fire({icon:'success',title:'Top Up Disetujui!',text:'Saldo user berhasil ditambahkan.',timer:1800,showConfirmButton:false}).then(()=>location.reload());
  } else {
    Swal.fire({icon:'error',title:'Gagal',text:data.message,confirmButtonColor:'#6C3CE1'});
  }
}

async function rejectTopup(id, nama) {
  const { value: reason } = await Swal.fire({
    title:'❌ Tolak Top Up?',
    html:`<p style="margin-bottom:12px">Top Up dari <strong>${nama}</strong> akan ditolak.</p>
          <textarea id="rejectReason" class="swal2-textarea" placeholder="Alasan penolakan (opsional)..." style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#fff;border-radius:10px;height:80px"></textarea>`,
    showCancelButton:true, confirmButtonText:'<i class="fas fa-times"></i> Ya, Tolak',
    cancelButtonText:'Batal',
    confirmButtonColor:'#EF4444', cancelButtonColor:'#374151',
    preConfirm: () => document.getElementById('rejectReason').value
  });
  if (reason === undefined) return;
  const resp = await fetch('../proses/admin_confirm.php', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`topup_id=${id}&action=reject&note=${encodeURIComponent(reason||'')}`});
  const data = await resp.json();
  if (data.success) {
    Swal.fire({icon:'success',title:'Top Up Ditolak',timer:1500,showConfirmButton:false}).then(()=>location.reload());
  } else {
    Swal.fire({icon:'error',title:'Gagal',text:data.message,confirmButtonColor:'#6C3CE1'});
  }
}
</script>


</body>
</html>
