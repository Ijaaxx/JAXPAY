<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
$user_id = $_SESSION['user_id'];
$user = $koneksi->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

$users = $koneksi->query("SELECT id,nama,email,role,member_id,saldo FROM users WHERE id != $user_id AND is_active=1 ORDER BY nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Transfer</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/mobile.css?v=5">
<style>
.search-bar {
  margin: 16px; position: relative;
}
.search-input {
  width:100%; background:var(--bg-input); border:1px solid var(--border);
  border-radius:16px; padding:14px 16px 14px 46px; font-size:15px;
  color:var(--text-primary); outline:none; font-family:inherit; transition:all 0.3s;
}
.search-input::placeholder { color:var(--text-muted); }
.search-input:focus { border-color:var(--primary-light); box-shadow:0 0 0 3px rgba(108,60,225,0.2); }
.search-icon { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--primary-light); }

.user-list { padding: 0 16px; }
.user-card {
  display:flex; align-items:center; gap:14px;
  background:var(--bg-card); border:1px solid var(--border); border-radius:16px;
  padding:14px; margin-bottom:10px; cursor:pointer; transition:all 0.2s;
}
.user-card:hover { background:var(--bg-card-hover); border-color:var(--primary); transform:translateX(4px); }
.user-card.selected { border-color:var(--primary-light); background:rgba(108,60,225,0.15); }
.user-avatar {
  width:46px; height:46px; border-radius:50%;
  background:linear-gradient(135deg,var(--primary),var(--accent));
  display:flex; align-items:center; justify-content:center;
  font-size:18px; font-weight:700; color:#fff; flex-shrink:0;
}
.user-info { flex:1; }
.user-info h4 { font-size:14px; font-weight:600; margin-bottom:3px; color:var(--text-primary); }
.user-info p { font-size:12px; color:var(--text-muted); }
.user-role { font-size:10px; padding:3px 8px; border-radius:20px; font-weight:700; }
.role-student { background:rgba(108,60,225,0.2); color:var(--primary-light); }
.role-teacher { background:rgba(0,212,255,0.2); color:var(--accent); }
.role-parent { background:rgba(16,185,129,0.2); color:#10B981; }
.role-merchant { background:rgba(251,191,36,0.2); color:#FBBF24; }

.amount-section { padding: 16px; }
.amount-label { font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:10px; }
.amount-input-wrap { position:relative; }
.amount-prefix { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-weight:700; font-size:15px; }
.amount-input {
  width:100%; background:var(--bg-input); border:1px solid var(--border);
  border-radius:16px; padding:16px 16px 16px 56px; font-size:22px;
  font-weight:700; color:var(--text-primary); outline:none; font-family:inherit; transition:all 0.3s;
}
.amount-input::placeholder { color:var(--text-muted); }
.amount-input:focus { border-color:var(--primary-light); box-shadow:0 0 0 3px rgba(108,60,225,0.2); }

.quick-amounts { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
.quick-amt {
  padding:8px 14px; border-radius:10px; border:1px solid var(--border);
  background:var(--bg-card); color:var(--text-secondary);
  font-size:12px; font-weight:600; cursor:pointer; transition:all 0.2s;
}
.quick-amt:hover { border-color:var(--primary-light); color:var(--primary-light); background:rgba(108,60,225,0.1); }
.quick-amt.active { border-color:var(--primary-light); color:var(--primary-light); background:rgba(108,60,225,0.15); }

.selected-user-card {
  margin:16px; background:rgba(108,60,225,0.1); border:1px solid rgba(108,60,225,0.3);
  border-radius:16px; padding:16px;
  display:none; align-items:center; gap:14px;
}
.selected-user-card.visible { display:flex; }

.note-input { resize:none; height:80px; }
</style>
  <link rel="stylesheet" href="../assets/css/theme.css?v=4">
  <script src="../assets/js/theme.js?v=3"></script>
</head>
<body>
<div class="phone-outer">
<div class="phone-frame">
  <div class="dynamic-island"><div class="di-speaker"></div><div class="di-camera"></div></div>
  <div class="status-bar"><span>9:41</span><div class="status-right"><i class="fas fa-signal"></i><i class="fas fa-wifi"></i><i class="fas fa-battery-full"></i></div></div>

  <div class="phone-content">
    <div class="page-header animate-up">
      <a href="home.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
      <div>
        <div class="page-title">Transfer</div>
        <div style="font-size:12px;color:var(--text-muted)">Saldo: Rp <?= number_format($user['saldo'],0,',','.') ?></div>
      </div>
    </div>

    <!-- Selected user preview -->
    <div class="selected-user-card animate-up-1" id="selectedCard">
      <div class="user-avatar" id="selectedAvatar">?</div>
      <div class="user-info">
        <h4 id="selectedName">-</h4>
        <p id="selectedEmail">-</p>
      </div>
      <button onclick="clearSelected()" style="background:rgba(239,68,68,0.2);border:none;color:#EF4444;border-radius:8px;padding:6px 10px;cursor:pointer;font-size:12px;">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Search -->
    <div class="search-bar animate-up-1" id="searchSection">
      <i class="fas fa-search search-icon"></i>
      <input type="text" class="search-input" id="searchInput" placeholder="Cari nama, email, atau member ID..." oninput="filterUsers()">
    </div>

    <!-- User List -->
    <div class="user-list animate-up-2" id="userList">
      <?php while($u = $users->fetch_assoc()):
        $inisial = strtoupper(substr($u['nama'],0,1));
        $rclass = 'role-'.$u['role'];
      ?>
      <div class="user-card" data-id="<?=$u['id']?>" data-nama="<?=htmlspecialchars($u['nama'])?>"
        data-email="<?=$u['email']?>" data-inisial="<?=$inisial?>"
        onclick="selectUser(this)">
        <div class="user-avatar"><?=$inisial?></div>
        <div class="user-info">
          <h4><?=htmlspecialchars($u['nama'])?></h4>
          <p><?=$u['email']?></p>
        </div>
        <span class="user-role <?=$rclass?>"><?=ucfirst($u['role'])?></span>
      </div>
      <?php endwhile; ?>
    </div>

    <!-- Amount Section -->
    <div class="amount-section animate-up-3" id="amountSection" style="display:none">
      <div class="amount-label">Jumlah Transfer</div>
      <div class="amount-input-wrap">
        <span class="amount-prefix">Rp</span>
        <input type="number" id="amountInput" class="amount-input" placeholder="0" min="1000" max="<?=$user['saldo']?>">
      </div>
      <div class="quick-amounts">
        <div class="quick-amt" onclick="setAmount(10000)">10.000</div>
        <div class="quick-amt" onclick="setAmount(20000)">20.000</div>
        <div class="quick-amt" onclick="setAmount(50000)">50.000</div>
        <div class="quick-amt" onclick="setAmount(100000)">100.000</div>
      </div>

      <div class="form-group" style="margin-top:16px">
        <label class="form-label">Catatan (Opsional)</label>
        <textarea id="noteInput" class="form-input note-input" placeholder="Tulis catatan..."></textarea>
      </div>

      <button class="btn-primary ripple-btn" onclick="doTransfer()" id="btnTransfer">
        <i class="fas fa-paper-plane"></i> Lanjut Transfer
      </button>
    </div>

    <div style="height:20px"></div>
  </div>

  <div class="bottom-nav">
    <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="mutasi.php" class="nav-item"><i class="fas fa-receipt"></i><span>Mutasi</span></a>
    <a href="qr.php" class="nav-qr"><i class="fas fa-qrcode"></i></a>
    <a href="merchant.php" class="nav-item"><i class="fas fa-store"></i><span>Merchant</span></a>
    <a href="profile.php" class="nav-item"><i class="fas fa-user"></i><span>Profil</span></a>
  </div>
</div>
</div>

<script>
let selectedUserId = null;
let selectedUserName = '';

function filterUsers() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('.user-card').forEach(card => {
    const text = (card.dataset.nama + card.dataset.email).toLowerCase();
    card.style.display = text.includes(q) ? '' : 'none';
  });
}

function selectUser(card) {
  selectedUserId = card.dataset.id;
  selectedUserName = card.dataset.nama;
  document.getElementById('selectedAvatar').textContent = card.dataset.inisial;
  document.getElementById('selectedName').textContent = card.dataset.nama;
  document.getElementById('selectedEmail').textContent = card.dataset.email;
  document.getElementById('selectedCard').classList.add('visible');
  document.getElementById('searchSection').style.display = 'none';
  document.getElementById('userList').style.display = 'none';
  document.getElementById('amountSection').style.display = 'block';
  document.getElementById('amountSection').style.animation = 'fadeInUp 0.4s ease';
}

function clearSelected() {
  selectedUserId = null;
  document.getElementById('selectedCard').classList.remove('visible');
  document.getElementById('searchSection').style.display = '';
  document.getElementById('userList').style.display = '';
  document.getElementById('amountSection').style.display = 'none';
  document.getElementById('amountInput').value = '';
}

function setAmount(val) {
  document.getElementById('amountInput').value = val;
  document.querySelectorAll('.quick-amt').forEach(el => el.classList.remove('active'));
  event.target.classList.add('active');
}

async function doTransfer() {
  const amount = parseInt(document.getElementById('amountInput').value);
  const note = document.getElementById('noteInput').value;
  const maxSaldo = <?= $user['saldo'] ?>;

  if (!selectedUserId) return Swal.fire({icon:'warning',title:'Pilih Penerima',text:'Pilih pengguna tujuan transfer.',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
  if (!amount || amount < 1000) return Swal.fire({icon:'warning',title:'Nominal Salah',text:'Minimal transfer Rp 1.000',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
  if (amount > maxSaldo) return Swal.fire({icon:'error',title:'Saldo Tidak Cukup',text:'Saldo Anda tidak mencukupi untuk transfer ini.',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});

  const result = await Swal.fire({
    title: 'Konfirmasi Transfer',
    html: `Transfer <strong>Rp ${amount.toLocaleString('id-ID')}</strong><br>ke <strong>${selectedUserName}</strong>?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-check"></i> Kirim',
    cancelButtonText: 'Batal',
    background: '#1A1A2E', color: '#fff',
    confirmButtonColor: '#6C3CE1', cancelButtonColor: '#374151'
  });
  if (!result.isConfirmed) return;

  const btn = document.getElementById('btnTransfer');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Memproses...';

  Swal.fire({title:'Memproses Transfer...',allowOutsideClick:false,didOpen:()=>Swal.showLoading(),background:'#1A1A2E',color:'#fff'});

  const resp = await fetch('../proses/transfer.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: `to_user_id=${selectedUserId}&jumlah=${amount}&catatan=${encodeURIComponent(note)}`
  });
  const data = await resp.json();

  if (data.success) {
    Swal.fire({
      icon:'success', title:'Transfer Berhasil! 🎉',
      html: `<strong>Rp ${amount.toLocaleString('id-ID')}</strong> berhasil dikirim ke <strong>${selectedUserName}</strong><br><small>Kode: ${data.kode}</small>`,
      background:'#1A1A2E', color:'#fff', confirmButtonColor:'#6C3CE1', confirmButtonText:'OK'
    }).then(() => window.location.href = 'home.php');
  } else {
    Swal.fire({icon:'error',title:'Gagal',text:data.message||'Terjadi kesalahan.',background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Lanjut Transfer';
  }
}
</script>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
