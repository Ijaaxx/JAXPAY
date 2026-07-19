/**
 * JAXPAY - Merchant Handler
 */

async function payMerchant(merchantId, merchantName, userSaldo) {
  const { value } = await Swal.fire({
    title: `💳 Bayar ke ${merchantName}`,
    html: `<div style="color:rgba(255,255,255,0.6);font-size:13px;margin-bottom:10px">Saldo: <strong>Rp ${parseInt(userSaldo).toLocaleString('id-ID')}</strong></div>
           <input id="swalAmount" type="number" class="swal2-input" placeholder="Nominal (Rp)..." min="1000" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#fff;border-radius:12px">
           <textarea id="swalNote" class="swal2-textarea" placeholder="Keterangan..." style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#fff;border-radius:12px;height:60px;margin-top:8px"></textarea>`,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-bolt"></i> Bayar',
    cancelButtonText: 'Batal',
    background: '#1A1A2E', color: '#fff',
    confirmButtonColor: '#6C3CE1', cancelButtonColor: '#374151',
    preConfirm: () => {
      const v = parseInt(document.getElementById('swalAmount').value);
      const n = document.getElementById('swalNote').value;
      if (!v || v < 1000) { Swal.showValidationMessage('Minimal Rp 1.000'); return false; }
      if (v > userSaldo) { Swal.showValidationMessage('Saldo tidak cukup!'); return false; }
      return { amount: v, note: n };
    }
  });

  if (!value) return;

  Swal.fire({ title:'Memproses...', allowOutsideClick:false, didOpen:()=>Swal.showLoading(), background:'#1A1A2E', color:'#fff' });

  const resp = await fetch('../proses/pembayaran.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `merchant_id=${merchantId}&jumlah=${value.amount}&catatan=${encodeURIComponent(value.note)}`
  });
  const data = await resp.json();

  if (data.success) {
    Swal.fire({
      icon: 'success', title: 'Pembayaran Berhasil! 🎉',
      html: `Rp ${value.amount.toLocaleString('id-ID')} ke <strong>${merchantName}</strong>`,
      background:'#1A1A2E', color:'#fff', confirmButtonColor:'#6C3CE1', timer:3000
    }).then(() => window.location.reload());
  } else {
    Swal.fire({ icon:'error', title:'Gagal', text:data.message, background:'#1A1A2E', color:'#fff', confirmButtonColor:'#6C3CE1' });
  }
}
