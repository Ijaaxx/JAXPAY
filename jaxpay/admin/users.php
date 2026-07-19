<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$filter_role = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$search      = isset($_GET['q'])    ? sanitize($_GET['q']) : '';
$where_parts = [];
if ($filter_role && in_array($filter_role, ['student','teacher','parent','merchant'])) $where_parts[] = "role='$filter_role'";
if ($search) $where_parts[] = "(nama LIKE '%$search%' OR email LIKE '%$search%' OR member_id LIKE '%$search%')";
$where = $where_parts ? 'WHERE '.implode(' AND ',$where_parts) : '';
$users = $koneksi->query("SELECT * FROM users $where ORDER BY created_at DESC");
$total = $koneksi->query("SELECT COUNT(*) as c FROM users $where")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY Admin - Kelola User</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/admin.css?v=2">
  <link rel="stylesheet" href="../assets/css/theme.css?v=4">

</head>
<body>
<?php include 'sidebar.php'; ?>
<?php include 'navbar.php'; ?>

<main class="admin-main animate-in">

  <!-- Header Row -->
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px">
    <div>
      <h2 style="font-size:20px;font-weight:800">Kelola Pengguna</h2>
      <p style="color:var(--text-muted);font-size:13px;margin-top:3px">Total <?= $total ?> pengguna terdaftar</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
      <i class="fas fa-user-plus"></i> Tambah User
    </button>
  </div>

  <!-- Users Table Card -->
  <div class="admin-card">
    <div class="table-controls">
      <div class="search-input-wrap">
        <i class="fas fa-search"></i>
        <input type="text" class="table-search" id="searchInput" placeholder="Cari nama, email, member ID..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <select class="table-select" id="roleFilter" onchange="doFilter()">
        <option value="">Semua Role</option>
        <option value="student"   <?= $filter_role==='student'  ?'selected':'' ?>>Student</option>
        <option value="teacher"   <?= $filter_role==='teacher'  ?'selected':'' ?>>Teacher</option>
        <option value="parent"    <?= $filter_role==='parent'   ?'selected':'' ?>>Parent</option>
        <option value="merchant"  <?= $filter_role==='merchant' ?'selected':'' ?>>Merchant</option>
      </select>
      <a href="users.php" class="btn btn-outline btn-sm"><i class="fas fa-rotate-right"></i> Reset</a>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Pengguna</th><th>Member ID</th><th>Role</th><th>Kelas</th>
            <th>Saldo</th><th>Level</th><th>Status</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php $no=1; $has_users=false; while($u = $users->fetch_assoc()): $has_users=true;
          $inisial = strtoupper(substr($u['nama'],0,1));
          $role_colors = ['student'=>'primary','teacher'=>'info','parent'=>'success','merchant'=>'warning'];
          $level_colors = ['Basic'=>'muted','Silver'=>'info','Gold'=>'warning','Platinum'=>'success'];
          $rc = $role_colors[$u['role']] ?? 'muted';
          $lc = $level_colors[$u['member_level']] ?? 'muted';
        ?>
        <tr>
          <td style="color:var(--text-muted)"><?= $no++ ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;flex-shrink:0"><?= $inisial ?></div>
              <div>
                <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($u['nama']) ?></div>
                <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($u['email']) ?></div>
              </div>
            </div>
          </td>
          <td><code style="font-size:11px;color:var(--accent)"><?= htmlspecialchars($u['member_id']) ?></code></td>
          <td><span class="badge badge-<?= $rc ?>"><?= ucfirst($u['role']) ?></span></td>
          <td style="font-size:12px"><?= htmlspecialchars($u['kelas']??'-') ?></td>
          <td style="font-weight:700;color:var(--success)">Rp <?= number_format($u['saldo'],0,',','.') ?></td>
          <td><span class="badge badge-<?= $lc ?>"><?= $u['member_level'] ?></span></td>
          <td>
            <?php if($u['is_active']): ?>
            <span class="badge badge-success"><i class="fas fa-circle" style="font-size:6px"></i> Aktif</span>
            <?php else: ?>
            <span class="badge badge-danger"><i class="fas fa-circle" style="font-size:6px"></i> Nonaktif</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:6px">
              <button class="btn btn-outline btn-sm btn-icon" onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)" title="Edit"><i class="fas fa-pen"></i></button>
              <button class="btn btn-warning btn-sm btn-icon" onclick="resetSaldo(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nama']) ?>')" title="Reset Saldo"><i class="fas fa-coins"></i></button>
              <button class="btn btn-danger btn-sm btn-icon" onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nama']) ?>')" title="Hapus"><i class="fas fa-trash"></i></button>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php if (!$has_users): ?>
        <tr>
          <td colspan="9">
            <div class="empty-state">
              <i class="fas fa-user-magnifying-glass"></i>
              <strong>Tidak ada pengguna ditemukan</strong>
              <span>Coba ubah kata kunci atau filter role.</span>
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

<!-- Add User Modal -->
<div class="modal-backdrop" id="addModal">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-user-plus" style="color:var(--primary-light)"></i> Tambah User Baru</h3>
      <button class="modal-close" onclick="closeModal('addModal')"><i class="fas fa-times"></i></button>
    </div>
    <form id="addUserForm">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nama Lengkap *</label>
          <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email *</label>
          <input type="email" name="email" class="form-control" placeholder="email@sekolah.id" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">No. HP</label>
          <input type="tel" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx">
        </div>
        <div class="form-group">
          <label class="form-label">Role *</label>
          <select name="role" class="form-control" required>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
            <option value="parent">Parent</option>
            <option value="merchant">Merchant</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">NIS/NIM</label>
          <input type="text" name="nis_nim" class="form-control" placeholder="Nomor induk">
        </div>
        <div class="form-group">
          <label class="form-label">Kelas/Jabatan</label>
          <input type="text" name="kelas" class="form-control" placeholder="XII IPA 1 / Staff">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Saldo Awal (Rp)</label>
        <input type="number" name="saldo" class="form-control" value="0" min="0" placeholder="0">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnAddUser"><i class="fas fa-check"></i> Simpan User</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal-backdrop" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-pen" style="color:var(--warning)"></i> Edit User</h3>
      <button class="modal-close" onclick="closeModal('editModal')"><i class="fas fa-times"></i></button>
    </div>
    <form id="editUserForm">
      <input type="hidden" name="user_id" id="editUserId">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nama Lengkap *</label>
          <input type="text" name="nama" id="editNama" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">No. HP</label>
          <input type="text" name="no_hp" id="editNoHp" class="form-control">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Role</label>
          <select name="role" id="editRole" class="form-control">
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
            <option value="parent">Parent</option>
            <option value="merchant">Merchant</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Kelas/Jabatan</label>
          <input type="text" name="kelas" id="editKelas" class="form-control">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Member Level</label>
          <select name="member_level" id="editLevel" class="form-control">
            <option value="Basic">Basic</option>
            <option value="Silver">Silver</option>
            <option value="Gold">Gold</option>
            <option value="Platinum">Platinum</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="is_active" id="editActive" class="form-control">
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
let searchTimer;
function doSearch() {
  const q = document.getElementById('searchInput').value;
  const role = document.getElementById('roleFilter').value;
  window.location.href = `users.php?q=${encodeURIComponent(q)}&role=${encodeURIComponent(role)}`;
}
document.getElementById('searchInput').addEventListener('input', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(doSearch, 450);
});
function doFilter() {
  const q = document.getElementById('searchInput').value;
  const role = document.getElementById('roleFilter').value;
  window.location.href = `users.php?q=${encodeURIComponent(q)}&role=${encodeURIComponent(role)}`;
}
function openAddModal() { document.getElementById('addModal').classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function editUser(u) {
  document.getElementById('editUserId').value = u.id;
  document.getElementById('editNama').value = u.nama;
  document.getElementById('editNoHp').value = u.no_hp||'';
  document.getElementById('editRole').value = u.role;
  document.getElementById('editKelas').value = u.kelas||'';
  document.getElementById('editLevel').value = u.member_level;
  document.getElementById('editActive').value = u.is_active;
  document.getElementById('editModal').classList.add('open');
}

async function deleteUser(id, nama) {
  const r = await Swal.fire({
    title:'Hapus User?', html:`Hapus akun <strong>${nama}</strong>?<br><small style="color:#EF4444">Semua data transaksi akan ikut terhapus!</small>`,
    icon:'warning', showCancelButton:true, confirmButtonText:'Ya, Hapus!', cancelButtonText:'Batal',
     confirmButtonColor:'#EF4444', cancelButtonColor:'#374151'
  });
  if (!r.isConfirmed) return;
  Swal.fire({title:'Menghapus...',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
  const resp = await fetch('ajax_users.php', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=delete&user_id=${id}`});
  const data = await resp.json();
  if (data.success) {
    Swal.fire({icon:'success',title:'Dihapus!',timer:1500,showConfirmButton:false}).then(()=>location.reload());
  } else {
    Swal.fire({icon:'error',title:'Gagal',text:data.message,confirmButtonColor:'#6C3CE1'});
  }
}

async function resetSaldo(id, nama) {
  const { value: newSaldo } = await Swal.fire({
    title:`Reset Saldo ${nama}`,
    html:`<input id="newSaldo" type="number" class="swal2-input" placeholder="Saldo baru..." min="0" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;border-radius:10px">`,
    showCancelButton:true, confirmButtonText:'Update Saldo', cancelButtonText:'Batal',
     confirmButtonColor:'#6C3CE1', cancelButtonColor:'#374151',
    preConfirm: () => { const v = document.getElementById('newSaldo').value; if(v==='') { Swal.showValidationMessage('Masukkan nominal!'); return false; } return v; }
  });
  if (newSaldo === undefined) return;
  const resp = await fetch('ajax_users.php', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=reset_saldo&user_id=${id}&saldo=${newSaldo}`});
  const data = await resp.json();
  Swal.fire({icon:data.success?'success':'error',title:data.success?'Saldo Diupdate!':data.message,timer:1800,showConfirmButton:false}).then(()=>{if(data.success)location.reload();});
}

// Add User Form
document.getElementById('addUserForm').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('btnAddUser');
  btn.disabled=true; btn.innerHTML='<i class="fas fa-circle-notch fa-spin"></i> Menyimpan...';
  const fd = new FormData(e.target);
  fd.append('action','add');
  const resp = await fetch('ajax_users.php', {method:'POST', body: new URLSearchParams(fd)});
  const data = await resp.json();
  if (data.success) {
    Swal.fire({icon:'success',title:'User Ditambahkan!',timer:1500,showConfirmButton:false}).then(()=>location.reload());
  } else {
    Swal.fire({icon:'error',title:'Gagal',text:data.message,confirmButtonColor:'#6C3CE1'});
    btn.disabled=false; btn.innerHTML='<i class="fas fa-check"></i> Simpan User';
  }
});

// Edit User Form
document.getElementById('editUserForm').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('action','edit');
  const resp = await fetch('ajax_users.php', {method:'POST', body: new URLSearchParams(fd)});
  const data = await resp.json();
  if (data.success) {
    Swal.fire({icon:'success',title:'User Diupdate!',timer:1500,showConfirmButton:false}).then(()=>location.reload());
  } else {
    Swal.fire({icon:'error',title:'Gagal',text:data.message,confirmButtonColor:'#6C3CE1'});
  }
});

// Close modal when clicking backdrop
document.querySelectorAll('.modal-backdrop').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});
</script>


</body>
</html>
