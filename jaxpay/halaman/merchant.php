<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
$user_id = $_SESSION['user_id'];
$user = $koneksi->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$merchantResult = $koneksi->query("SELECT m.*, u.nama, u.email FROM merchant m JOIN users u ON m.user_id=u.id WHERE m.is_active=1 ORDER BY m.total_omzet DESC");
$merchants = [];
$hasPakaian = false;
while ($row = $merchantResult->fetch_assoc()) {
  $merchants[] = $row;
  if ($row['kategori'] === 'Pakaian') {
    $hasPakaian = true;
  }
}
if (!$hasPakaian) {
  $merchants[] = [
    'id' => 0,
    'nama_toko' => 'Butik Serba Gaya',
    'deskripsi' => 'Toko pakaian sekolah, seragam, dan gaya kasual anak muda.',
    'kategori' => 'Pakaian',
    'is_active' => 1,
    'total_transaksi' => 86,
    'total_omzet' => 3100000,
    'nama' => 'JAXPAY Demo',
    'email' => 'info@jaxpay.id'
  ];
}
$emojis = ['Makanan & Minuman'=>'🍱','Peralatan'=>'📐','Pakaian'=>'👕','Lainnya'=>'🏪'];
$colors = ['Makanan & Minuman'=>'linear-gradient(135deg,#f59e0b,#ef4444)','Peralatan'=>'linear-gradient(135deg,#6C3CE1,#00D4FF)','Pakaian'=>'linear-gradient(135deg,#EC4899,#8B5CF6)','Lainnya'=>'linear-gradient(135deg,#10B981,#06B6D4)'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Merchant</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../assets/css/mobile.css?v=5">
<style>
.merchant-search { margin: 14px 16px; position: relative; }
.merchant-search input { width:100%; background:rgba(255,255,255,0.07); border:1px solid var(--border); border-radius:14px; padding:13px 16px 13px 44px; font-size:14px; color:#fff; outline:none; font-family:inherit; }
.merchant-search input::placeholder { color:rgba(255,255,255,0.3); }
.merchant-search i { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--primary-light); }

.category-scroll { display:flex; gap:8px; padding:0 16px 14px; overflow-x:auto; scrollbar-width:none; }
.category-scroll::-webkit-scrollbar { display:none; }
.cat-chip { flex-shrink:0; padding:7px 14px; border-radius:20px; border:1px solid var(--border); background:transparent; color:var(--text-muted); font-size:12px; font-weight:600; cursor:pointer; transition:all 0.2s; font-family:inherit; }
.cat-chip.active { background:var(--primary); color:#fff; border-color:var(--primary); }

.merchant-grid { padding: 0 16px; display: grid; gap: 12px; }
.merchant-card {
  background: var(--card); border: 1px solid var(--border);
  border-radius: 20px; overflow: hidden; cursor: pointer; transition: all 0.25s;
}
.merchant-card:hover { border-color: rgba(108,60,225,0.4); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
.merchant-banner {
  height: 80px; display: flex; align-items: center; justify-content: center;
  font-size: 36px; position: relative;
}
.merchant-status {
  position: absolute; top: 8px; right: 8px; padding: 4px 10px;
  border-radius: 20px; font-size: 10px; font-weight: 700;
}
.merchant-status.open { background: rgba(16,185,129,0.9); color: #fff; }
.merchant-status.closed { background: rgba(239,68,68,0.9); color: #fff; }
.merchant-body { padding: 14px; }
.merchant-name { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
.merchant-desc { font-size: 12px; color: var(--text-muted); margin-bottom: 10px; line-height: 1.4; }
.merchant-meta { display: flex; gap: 12px; }
.merchant-meta span { font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
.merchant-meta i { color: var(--primary-light); font-size: 11px; }

.btn-pay-merchant {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  background: linear-gradient(135deg, var(--primary), var(--primary-light));
  color: #fff; border: none; border-radius: 12px; padding: 10px 16px;
  font-size: 13px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 10px;
  font-family: inherit; transition: all 0.2s;
}
.btn-pay-merchant:hover { opacity: 0.9; }
.btn-pay-merchant.disabled {
  opacity: 0.55; cursor: not-allowed; background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.75); border: 1px solid rgba(255,255,255,0.12);
}
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
        <div class="page-title">Merchant</div>
        <div style="font-size:12px;color:var(--text-muted)">Saldo: Rp <?= number_format($user['saldo'],0,',','.') ?></div>
      </div>
    </div>

    <div class="merchant-search animate-up-1">
      <i class="fas fa-search"></i>
      <input type="text" id="searchMerchant" placeholder="Cari merchant..." oninput="filterMerchants()">
    </div>

    <div class="category-scroll animate-up-1">
      <button class="cat-chip active" onclick="filterCat('semua',this)">Semua</button>
      <button class="cat-chip" onclick="filterCat('Makanan & Minuman',this)">🍱 Makanan</button>
      <button class="cat-chip" onclick="filterCat('Peralatan',this)">📐 Peralatan</button>
      <button class="cat-chip" onclick="filterCat('Pakaian',this)">👕 Pakaian</button>
    </div>

    <div class="merchant-grid animate-up-2" id="merchantGrid">
      <?php foreach ($merchants as $m):
        $emoji = $emojis[$m['kategori']] ?? '🏪';
        $bg = $colors[$m['kategori']] ?? 'linear-gradient(135deg,#374151,#6B7280)';
      ?>
      <div class="merchant-card" data-cat="<?= htmlspecialchars($m['kategori']) ?>" data-nama="<?= htmlspecialchars(strtolower($m['nama_toko'])) ?>">
        <div class="merchant-banner" style="background:<?= $bg ?>">
          <?= $emoji ?>
          <div class="merchant-status open">Buka</div>
        </div>
        <div class="merchant-body">
          <div class="merchant-name"><?= htmlspecialchars($m['nama_toko']) ?></div>
          <div class="merchant-desc"><?= htmlspecialchars($m['deskripsi']) ?></div>
          <div class="merchant-meta">
            <span><i class="fas fa-tag"></i><?= htmlspecialchars($m['kategori']) ?></span>
            <span><i class="fas fa-shopping-bag"></i><?= number_format($m['total_transaksi'],0,',','.') ?>x transaksi</span>
          </div>
          <?php if ($m['id'] > 0): ?>
            <button class="btn-pay-merchant" onclick="payMerchant(<?= $m['id'] ?>, '<?= htmlspecialchars($m['nama_toko']) ?>', <?= $user['saldo'] ?>)">
              <i class="fas fa-bolt"></i> Bayar Sekarang
            </button>
          <?php else: ?>
            <button class="btn-pay-merchant disabled" onclick="showComingSoon()" type="button">
              <i class="fas fa-clock"></i> Segera Hadir
            </button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="height:20px"></div>
  </div>

  <div class="bottom-nav">
    <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="mutasi.php" class="nav-item"><i class="fas fa-receipt"></i><span>Mutasi</span></a>
    <a href="qr.php" class="nav-qr"><i class="fas fa-qrcode"></i></a>
    <a href="merchant.php" class="nav-item active"><i class="fas fa-store"></i><span>Merchant</span></a>
    <a href="profile.php" class="nav-item"><i class="fas fa-user"></i><span>Profil</span></a>
  </div>
</div>
</div>

<script>
function filterMerchants() {
  const q = document.getElementById('searchMerchant').value.toLowerCase();
  document.querySelectorAll('.merchant-card').forEach(c => {
    c.style.display = c.dataset.nama.includes(q) ? '' : 'none';
  });
}
function filterCat(cat, btn) {
  document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.merchant-card').forEach(c => {
    c.style.display = (cat === 'semua' || c.dataset.cat === cat) ? '' : 'none';
  });
}
async function payMerchant(merchantId, merchantName, userSaldo) {
  const { value: amount } = await Swal.fire({
    title: `Bayar ke ${merchantName}`,
    html: `<div style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:12px">Saldo Anda: <strong>Rp ${userSaldo.toLocaleString('id-ID')}</strong></div>
           <input id="payAmt" type="number" class="swal2-input" placeholder="Masukkan nominal..." min="1000" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;border-radius:12px">
           <textarea id="payNote" class="swal2-textarea" placeholder="Keterangan (opsional)..." style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;border-radius:12px;margin-top:8px;height:70px"></textarea>`,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-bolt"></i> Bayar',
    cancelButtonText: 'Batal',
    background: '#1A1A2E', color: '#fff',
    confirmButtonColor: '#6C3CE1', cancelButtonColor: '#374151',
    preConfirm: () => {
      const v = parseInt(document.getElementById('payAmt').value);
      if (!v || v < 1000) { Swal.showValidationMessage('Minimal pembayaran Rp 1.000'); return false; }
      if (v > userSaldo) { Swal.showValidationMessage('Saldo tidak cukup!'); return false; }
      return { amount: v, note: document.getElementById('payNote').value };
    }
  });
  if (!amount) return;

  Swal.fire({title:'Memproses Pembayaran...',allowOutsideClick:false,didOpen:()=>Swal.showLoading(),background:'#1A1A2E',color:'#fff'});

  const resp = await fetch('../proses/pembayaran.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`merchant_id=${merchantId}&jumlah=${amount.amount}&catatan=${encodeURIComponent(amount.note)}`
  });
  const data = await resp.json();

  if (data.success) {
    Swal.fire({icon:'success',title:'Pembayaran Berhasil! 🎉',
      html:`Rp ${amount.amount.toLocaleString('id-ID')} ke <strong>${merchantName}</strong><br><small>Kode: ${data.kode}</small>`,
      background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1',timer:3000
    }).then(()=>window.location.reload());
  } else {
    Swal.fire({icon:'error',title:'Gagal',text:data.message,background:'#1A1A2E',color:'#fff',confirmButtonColor:'#6C3CE1'});
  }
}
function showComingSoon() {
  Swal.fire({
    icon: 'info',
    title: 'Pakaian Segera Hadir',
    text: 'Kategori pakaian sedang ditambahkan. Tunggu sedikit lagi ya!',
    background: '#1A1A2E', color: '#fff', confirmButtonColor: '#6C3CE1'
  });
}
</script>

<button class="theme-toggle-mobile" data-theme-toggle title="Toggle Theme">
  <i class="fas fa-sun"></i>
</button>

</body>
</html>
