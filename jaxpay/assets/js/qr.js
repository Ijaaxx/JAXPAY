/**
 * JAXPAY - QR Handler
 */

class QRManager {
  constructor(containerId, userData) {
    this.containerId = containerId;
    this.userData    = userData;
    this.instance    = null;
    this.timerObj    = null;
  }

  generate(extraData = {}) {
    const container = document.getElementById(this.containerId);
    if (!container) return;
    container.innerHTML = '';

    const data = {
      type: 'jaxpay_user',
      user_id: this.userData.id,
      member_id: this.userData.member_id,
      nama: this.userData.nama,
      ts: Date.now(),
      ...extraData
    };

    if (typeof QRCode !== 'undefined') {
      this.instance = new QRCode(container, {
        text: JSON.stringify(data),
        width: 200, height: 200,
        colorDark: '#1a0050',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
      });
    }
    return data;
  }

  refresh() {
    this.generate();
    if (this.timerObj) this.timerObj.reset(300);
  }

  startTimer(displayId, onExpire) {
    const display = document.getElementById(displayId);
    if (!display) return;
    let seconds = 300;
    this.timerObj = {
      reset: (s) => { seconds = s; display.style.color = ''; },
      interval: setInterval(() => {
        seconds--;
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        display.textContent = `${m}:${s}`;
        if (seconds <= 0) {
          clearInterval(this.timerObj.interval);
          display.textContent = 'Kadaluarsa';
          display.style.color = '#EF4444';
          if (typeof onExpire === 'function') onExpire();
        }
      }, 1000)
    };
  }
}
