<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$merchants = $koneksi->query("SELECT m.*, u.nama, u.email, u.saldo FROM merchant m JOIN users u ON m.user_id=u.id ORDER BY m.total_omzet DESC");
$merchant_users = $koneksi->query("SELECT id, nama, email FROM users WHERE role='merchant' AND is_active=1");
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY Admin - Merchant</title>
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
      <h2 style="font-size:20px;font-weight:800">Manajemen Merchant</h2>
      <p style="color:var(--text-muted);font-size:13px;margin-top:3px">Kelola toko & merchant yang terdaftar di JAXPAY</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addMerchantModal').classList.add('open')">
      <i class="fas fa-store"></i> Tambah Merchant
    </button>
  </div>

  <div class="admin-card">
    <div class="table-wrapper">
      <table>
        <thead>
          <tr><th>#</th><th>Toko</th><th>Pemilik</th><th>Kategori</th><th>Total Transaksi</th><th>Total Omzet</th><th>Saldo</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php $no=1; $has_merchants=false; while($m = $merchants->fetch_assoc()): $has_merchants=true;
          $cat_emoji = ['Makanan & Minuman'=>'🍱','Peralatan'=>'📐','Pakaian'=>'👕'];
          $emoji = $cat_emoji[$m['kategori']] ?? '🏪';
        ?>
        <tr>
          <td style="color:var(--text-muted)"><?= $no++ ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="font-size:28px"><?= $emoji ?></div>
              <div>
                <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($m['nama_toko']) ?></div>
                <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars(substr($m['deskripsi']??'',0,40)) ?><?= strlen($m['deskripsi']??'')>40?'...':'' ?></div>
              </div>
            </div>
          </td>
          <td>
            <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($m['nama']) ?></div>
            <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($m['email']) ?></div>
          </td>
          <td><span class="badge badge-primary"><?= htmlspecialchars($m['kategori']) ?></span></td>
          <td style="font-weight:700;color:var(--accent)"><?= number_format($m['total_transaksi'],0,',','.') ?>x</td>
          <td style="font-weight:700;color:var(--success)">Rp <?= number_format($m['total_omzet'],0,',','.') ?></td>
          <td style="font-weight:600">Rp <?= number_format($m['saldo'],0,',','.') ?></td>
          <td>
            <?php if ($m['is_active']): ?>
            <span class="badge badge-success"><i class="fas fa-circle" style="font-size:6px"></i> Aktif</span>
            <?php else: ?>
            <span class="badge badge-danger"><i class="fas fa-circle" style="font-size:6px"></i> Nonaktif</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:6px">
              <button class="btn btn-outline btn-sm btn-icon" onclick="editMerchant(<?= htmlspecialchars(json_encode($m)) ?>)" title="Edit"><i class="fas fa-pen"></i></button>
              <button class="btn btn-<?= $m['is_active']?'warning':'success' ?> btn-sm" onclick="toggleMerchant(<?= $m['id'] ?>, <?= $m['is_active'] ?>)">
                <?= $m['is_active']?'<i class="fas fa-pause"></i> Nonaktifkan':'<i class="fas fa-play"></i> Aktifkan' ?>
              </button>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php if (!$has_merchants): ?>
        <tr>
          <td colspan="9">
            <div class="empty-state">
              <i class="fas fa-store"></i>
              <strong>Belum ada merchant</strong>
              <span>Tambahkan merchant pertama untuk mulai memantau toko.</span>
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

<!-- Add Merchant Modal -->
<div class="modal-backdrop" id="addMerchantModal">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-store" style="color:var(--warning)"></i> Tambah Merchant</h3>
      <button class="modal-close" onclick="document.getElementById('addMerchantModal').classList.remove('open')"><i class="fas fa-times"></i></button>
    </div>
    <form id="addMerchantForm">
      <div class="form-group">
        <label class="form-label">User Merchant *</label>
        <select name="user_id" class="form-control" required>
          <option value="">-- Pilih User (role: merchant) --</option>
          <?php $merchant_users->data_seek(0); while($mu = $merchant_users->fetch_assoc()): ?>
          <option value="<?= $mu['id'] ?>"><?= htmlspecialchars($mu['nama']) ?> (<?= $mu['email'] ?>)</option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Nama Toko *</label>
        <input type="text" name="nama_toko" class="form-control" placeholder="Nama toko/warung" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Kategori</label>
          <select name="kategori" class="form-control">
            <option value="Makanan & Minuman">Makanan & Minuman</option>
            <option value="Peralatan">Peralatan</option>
            <option value="Pakaian">Pakaian</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Deskripsi</label>
        <textarea name="deskripsi" class="form-control" placeholder="Deskripsi singkat toko..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('addMerchantModal').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Merchant Modal -->
<div class="modal-backdrop" id="editMerchantModal">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-pen" style="color:var(--warning)"></i> Edit Merchant</h3>
      <button class="modal-close" onclick="document.getElementById('editMerchantModal').classList.remove('open')"><i class="fas fa-times"></i></button>
    </div>
    <form id="editMerchantForm">
      <input type="hidden" name="merchant_id" id="editMerchantId">
      <div class="form-group"><label class="form-label">Nama Toko *</label><input type="text" name="nama_toko" id="editNamaToko" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Kategori</label>
        <select name="kategori" id="editKategori" class="form-control">
          <option value="Makanan & Minuman">Makanan & Minuman</option>
          <option value="Peralatan">Peralatan</option>
          <option value="Pakaian">Pakaian</option>
          <option value="Lainnya">Lainnya</option>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Deskripsi</label><textarea name="deskripsi" id="editDeskripsi" class="form-control"></textarea></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('editMerchantModal').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('.modal-backdrop').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));

function editMerchant(m) {
  document.getElementById('editMerchantId').value = m.id;
  document.getElementById('editNamaToko').value = m.nama_toko;
  document.getElementById('editKategori').value = m.kategori||'Lainnya';
  document.getElementById('editDeskripsi').value = m.deskripsi||'';
  document.getElementById('editMerchantModal').classList.add('open');
}

async function toggleMerchant(id, currentActive) {
  const action = currentActive ? 'Nonaktifkan' : 'Aktifkan';
  const r = await Swal.fire({title:`${action} Merchant?`,icon:'question',showCancelButton:true,confirmButtonText:'Ya',cancelButtonText:'Batal',confirmButtonColor:'#6C3CE1',cancelButtonColor:'#374151'});
  if (!r.isConfirmed) return;
  const resp = await fetch('ajax_merchant.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=toggle&merchant_id=${id}&active=${currentActive?0:1}`});
  const data = await resp.json();
  if (data.success) { Swal.fire({icon:'success',title:'Berhasil!',timer:1200,showConfirmButton:false}).then(()=>location.reload()); }
  else Swal.fire({icon:'error',title:'Gagal',text:data.message});
}

document.getElementById('addMerchantForm').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target); fd.append('action','add');
  const resp = await fetch('ajax_merchant.php',{method:'POST',body:new URLSearchParams(fd)});
  const data = await resp.json();
  if (data.success) { Swal.fire({icon:'success',title:'Merchant Ditambahkan!',timer:1500,showConfirmButton:false}).then(()=>location.reload()); }
  else Swal.fire({icon:'error',title:'Gagal',text:data.message,confirmButtonColor:'#6C3CE1'});
});

document.getElementById('editMerchantForm').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target); fd.append('action','edit');
  const resp = await fetch('ajax_merchant.php',{method:'POST',body:new URLSearchParams(fd)});
  const data = await resp.json();
  if (data.success) { Swal.fire({icon:'success',title:'Disimpan!',timer:1300,showConfirmButton:false}).then(()=>location.reload()); }
  else Swal.fire({icon:'error',title:'Gagal',text:data.message,confirmButtonColor:'#6C3CE1'});
});
</script>


</body>
</html>
