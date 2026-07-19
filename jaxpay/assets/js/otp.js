/**
 * JAXPAY - OTP Handler
 */

class OTPManager {
  constructor(containerSelector, length = 6) {
    this.container = document.querySelector(containerSelector);
    this.length    = length;
    this.inputs    = [];
    if (this.container) this.init();
  }

  init() {
    this.inputs = Array.from(this.container.querySelectorAll('input.otp-input'));
    this.inputs.forEach((inp, i) => {
      inp.addEventListener('input',   e => this.onInput(e, i));
      inp.addEventListener('keydown', e => this.onKeydown(e, i));
      inp.addEventListener('paste',   e => this.onPaste(e));
      inp.addEventListener('focus',   () => inp.select());
    });
  }

  onInput(e, i) {
    const val = e.target.value.replace(/\D/g, '').slice(0, 1);
    e.target.value = val;
    val ? e.target.classList.add('filled') : e.target.classList.remove('filled');
    if (val && i < this.length - 1) this.inputs[i + 1].focus();
    this.onChange();
  }

  onKeydown(e, i) {
    if (e.key === 'Backspace' && !e.target.value && i > 0) {
      this.inputs[i - 1].value = '';
      this.inputs[i - 1].classList.remove('filled');
      this.inputs[i - 1].focus();
    }
    if (e.key === 'ArrowLeft' && i > 0) this.inputs[i - 1].focus();
    if (e.key === 'ArrowRight' && i < this.length - 1) this.inputs[i + 1].focus();
  }

  onPaste(e) {
    const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, this.length);
    text.split('').forEach((ch, j) => {
      if (this.inputs[j]) {
        this.inputs[j].value = ch;
        this.inputs[j].classList.add('filled');
      }
    });
    if (text.length === this.length) this.inputs[this.length - 1].focus();
    this.onChange();
    e.preventDefault();
  }

  getValue() {
    return this.inputs.map(i => i.value).join('');
  }

  isComplete() {
    return this.getValue().length === this.length;
  }

  onChange() {
    const btn = document.getElementById('btnVerify');
    if (btn) btn.disabled = !this.isComplete();
    if (this.isComplete() && typeof this.onComplete === 'function') this.onComplete(this.getValue());
  }

  shake() {
    this.inputs.forEach(inp => {
      inp.style.animation = 'shake 0.4s ease';
      setTimeout(() => inp.style.animation = '', 400);
    });
  }

  clear() {
    this.inputs.forEach(inp => { inp.value = ''; inp.classList.remove('filled'); });
    if (this.inputs[0]) this.inputs[0].focus();
  }
}

// ── Timer ──────────────────────────────────────────────────────────────────────
class OTPTimer {
  constructor(displayId, seconds, onExpire) {
    this.displayEl = document.getElementById(displayId);
    this.seconds   = seconds;
    this.onExpire  = onExpire;
    this.interval  = null;
  }

  start() {
    this.interval = setInterval(() => {
      this.seconds--;
      const m = String(Math.floor(this.seconds / 60)).padStart(2, '0');
      const s = String(this.seconds % 60).padStart(2, '0');
      if (this.displayEl) this.displayEl.textContent = `${m}:${s}`;
      if (this.seconds <= 0) {
        clearInterval(this.interval);
        if (this.displayEl) { this.displayEl.textContent = '00:00'; this.displayEl.style.color = '#EF4444'; }
        if (typeof this.onExpire === 'function') this.onExpire();
      }
    }, 1000);
  }

  reset(seconds) {
    clearInterval(this.interval);
    this.seconds = seconds;
    if (this.displayEl) this.displayEl.style.color = '';
    this.start();
  }
}
