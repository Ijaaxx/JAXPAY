/**
 * JAXPAY - Main App JavaScript
 * Splash screen, animations, utilities
 */

// ─── SPLASH SCREEN ───────────────────────────────────────────────────────────
(function () {
  const isMobilePage = document.querySelector(".phone-frame");
  if (!isMobilePage) return;

  // Only show splash on home page first load
  const isHome = window.location.pathname.includes("home.php");
  const shownBefore = sessionStorage.getItem("jaxpay_splash_shown");
  if (!isHome || shownBefore) return;

  sessionStorage.setItem("jaxpay_splash_shown", "1");

  const splash = document.createElement("div");
  splash.id = "jaxpay-splash";
  splash.innerHTML = `
    <div class="splash-inner">
      <div class="splash-logo">
        <img src="../assets/img/logo.png" alt="JAXPAY" class="splash-img">
        <div class="splash-name">JAXPAY</div>
        <div class="splash-tagline">School Digital Wallet</div>
      </div>
      <div class="splash-dots">
        <div class="dot"></div><div class="dot"></div><div class="dot"></div>
      </div>
    </div>
  `;

  const style = document.createElement("style");
  style.textContent = `
    #jaxpay-splash {
      position: absolute; inset: 0; z-index: 9999;
      background: linear-gradient(160deg, #0D0D1A 0%, #1a0050 50%, #0D0D1A 100%);
      display: flex; align-items: center; justify-content: center;
      border-radius: inherit;
      animation: splashFadeOut 0.6s ease 2.2s forwards;
    }
    .splash-inner { text-align: center; }
    .splash-logo { animation: splashPop 0.6s cubic-bezier(0.34,1.56,0.64,1) 0.2s both; }
    .splash-img { width: 88px; height: 88px; border-radius: 20px; display:block; margin: 0 auto 18px; object-fit:contain; }
    .splash-icon { display:none }
    @keyframes splashGlow {
      from { box-shadow: 0 0 40px rgba(108,60,225,0.6); }
      to   { box-shadow: 0 0 80px rgba(0,212,255,0.8), 0 0 40px rgba(108,60,225,0.8); }
    }
    .splash-name {
      font-size: 36px; font-weight: 900; letter-spacing: 4px; color: #fff;
      font-family: 'Segoe UI', system-ui, sans-serif;
    }
    .splash-tagline { font-size: 13px; color: rgba(255,255,255,0.5); margin-top: 6px; letter-spacing: 2px; }
    .splash-dots { display: flex; gap: 8px; justify-content: center; margin-top: 40px; }
    .dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: rgba(255,255,255,0.3);
      animation: dotPulse 1.2s ease-in-out infinite;
    }
    .dot:nth-child(1) { animation-delay: 0s; }
    .dot:nth-child(2) { animation-delay: 0.2s; }
    .dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes dotPulse {
      0%,80%,100% { background: rgba(255,255,255,0.2); transform: scale(1); }
      40% { background: #6C3CE1; transform: scale(1.4); }
    }
    @keyframes splashPop {
      from { opacity:0; transform: scale(0.7) translateY(20px); }
      to   { opacity:1; transform: scale(1) translateY(0); }
    }
    @keyframes splashFadeOut {
      from { opacity:1; pointer-events:auto; }
      to   { opacity:0; pointer-events:none; }
    }
  `;
  document.head.appendChild(style);

  const frame = document.querySelector(".phone-frame");
  if (frame) frame.appendChild(splash);

  setTimeout(() => {
    splash.remove();
    style.remove();
  }, 3000);
})();

// ─── RIPPLE EFFECT ───────────────────────────────────────────────────────────
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".btn-primary, .ripple-btn");
  if (!btn) return;
  const circle = document.createElement("span");
  const rect = btn.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  circle.style.cssText = `
    position:absolute;
    border-radius:50%;
    width:${size}px; height:${size}px;
    left:${e.clientX - rect.left - size / 2}px;
    top:${e.clientY - rect.top - size / 2}px;
    background:rgba(255,255,255,0.25);
    transform:scale(0); opacity:1;
    animation:rippleEffect 0.6s linear forwards;
    pointer-events:none;
  `;
  if (!document.getElementById("ripple-style")) {
    const rs = document.createElement("style");
    rs.id = "ripple-style";
    rs.textContent =
      "@keyframes rippleEffect{to{transform:scale(4);opacity:0}}";
    document.head.appendChild(rs);
  }
  const prevPos = btn.style.position;
  btn.style.position = "relative";
  btn.style.overflow = "hidden";
  btn.appendChild(circle);
  setTimeout(() => {
    circle.remove();
    if (!prevPos) btn.style.position = "";
  }, 700);
});

// ─── BALANCE COUNTER ANIMATION ────────────────────────────────────────────────
function animateCounter(el, target, duration = 1200) {
  const start = 0;
  const startTime = performance.now();
  const formatter = (n) => "Rp " + Math.floor(n).toLocaleString("id-ID");

  function step(now) {
    const elapsed = now - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
    el.textContent = formatter(start + (target - start) * eased);
    if (progress < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

// Auto-animate balance display on home page
document.addEventListener("DOMContentLoaded", () => {
  const balanceEl = document.getElementById("balanceDisplay");
  if (balanceEl) {
    const text = balanceEl.textContent.replace(/[^0-9]/g, "");
    const target = parseInt(text) || 0;
    setTimeout(() => animateCounter(balanceEl, target, 1400), 800);
  }
});

// ─── SCROLL REVEAL ────────────────────────────────────────────────────────────
const revealObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) {
        e.target.classList.add("visible");
        revealObserver.unobserve(e.target);
      }
    });
  },
  { threshold: 0.12 },
);

document
  .querySelectorAll(".reveal")
  .forEach((el) => revealObserver.observe(el));

// ─── TOAST HELPER ─────────────────────────────────────────────────────────────
window.toast = function (message, icon = "success") {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      toast: true,
      position: "top",
      icon,
      title: message,
      timer: 2000,
      showConfirmButton: false,
      background: "#1A1A2E",
      color: "#fff",
    });
  }
};

// ─── CURRENCY FORMATTER ────────────────────────────────────────────────────────
window.rupiah = (n) => "Rp " + parseInt(n).toLocaleString("id-ID");

// ─── AUTO-FORMAT NUMBER INPUTS ────────────────────────────────────────────────
document.querySelectorAll('input[type="number"]').forEach((inp) => {
  inp.addEventListener("wheel", (e) => e.preventDefault());
});

// ─── ACTIVE NAV ITEM HIGHLIGHT ────────────────────────────────────────────────
document.querySelectorAll(".nav-item").forEach((item) => {
  item.addEventListener("click", function () {
    document
      .querySelectorAll(".nav-item")
      .forEach((n) => n.classList.remove("active"));
    this.classList.add("active");
  });
});

// ─── SMOOTH PAGE TRANSITIONS ──────────────────────────────────────────────────
document.querySelectorAll("a[href]:not([target])").forEach((link) => {
  const href = link.getAttribute("href");
  if (
    !href ||
    href.startsWith("#") ||
    href.startsWith("javascript") ||
    href.startsWith("http")
  )
    return;
  link.addEventListener("click", function (e) {
    const content = document.querySelector(".phone-content");
    if (content) {
      e.preventDefault();
      content.style.transition = "opacity 0.2s ease";
      content.style.opacity = "0";
      setTimeout(() => (window.location.href = href), 200);
    }
  });
});

console.log(
  "%c⚡ JAXPAY Digital Wallet v1.0",
  "background:linear-gradient(135deg,#6C3CE1,#00D4FF);color:#fff;font-size:14px;font-weight:bold;padding:8px 16px;border-radius:8px",
);
