/**
 * JAXPAY - Transfer Handler
 */

class TransferManager {
  constructor() {
    this.selectedUser = null;
    this.amount       = 0;
  }

  selectUser(userData) {
    this.selectedUser = userData;
    document.dispatchEvent(new CustomEvent('jaxpay:user-selected', { detail: userData }));
  }

  setAmount(amount) {
    this.amount = amount;
  }

  validate(userSaldo) {
    if (!this.selectedUser) return { ok: false, msg: 'Pilih penerima terlebih dahulu' };
    if (!this.amount || this.amount < 1000) return { ok: false, msg: 'Nominal minimal Rp 1.000' };
    if (this.amount > userSaldo) return { ok: false, msg: 'Saldo Anda tidak mencukupi' };
    return { ok: true };
  }

  async execute(catatan = '') {
    const body = `to_user_id=${this.selectedUser.id}&jumlah=${this.amount}&catatan=${encodeURIComponent(catatan)}`;
    const resp = await fetch('../proses/transfer.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body
    });
    return resp.json();
  }
}

// Quick amount setter with active state toggle
function setQuickAmount(inputId, value, btnEl) {
  document.getElementById(inputId).value = value;
  document.querySelectorAll('.quick-amt').forEach(b => b.classList.remove('active'));
  if (btnEl) btnEl.classList.add('active');
}
