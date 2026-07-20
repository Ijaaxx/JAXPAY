/**
 * JAXPAY Theme Manager
 * Global dark / light theme with localStorage persistence.
 */

const ThemeManager = (function () {
  const STORAGE_KEY = "jaxpay_theme";
  const DARK = "dark";
  const LIGHT = "light";

  function getSaved() {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      return saved === LIGHT || saved === DARK ? saved : DARK;
    } catch {
      return DARK;
    }
  }

  function save(theme) {
    try {
      localStorage.setItem(STORAGE_KEY, theme);
    } catch {}
  }

  function themedValue(name, fallback) {
    return (
      getComputedStyle(document.documentElement)
        .getPropertyValue(name)
        .trim() || fallback
    );
  }

  function apply(theme) {
    theme = theme === LIGHT ? LIGHT : DARK;

    document.documentElement.setAttribute("data-theme", theme);
    document.documentElement.classList.toggle("dark", theme === DARK);
    document.documentElement.style.colorScheme = theme;

    if (document.body) {
      document.body.setAttribute("data-theme", theme);
      document.body.classList.toggle("dark", theme === DARK);
    }

    document.querySelectorAll("[data-theme-toggle]").forEach((btn) => {
      const icon = btn.querySelector("i") || btn;
      const label = btn.querySelector("[data-theme-label]");
      const isDark = theme === DARK;
      if (icon.className !== undefined)
        icon.className = isDark ? "fas fa-sun" : "fas fa-moon";
      if (label) label.textContent = isDark ? "Mode Terang" : "Mode Gelap";
      btn.title = isDark ? "Aktifkan Mode Terang" : "Aktifkan Mode Gelap";
      btn.setAttribute("aria-label", btn.title);
    });

    save(theme);
    window.dispatchEvent(
      new CustomEvent("jaxpay:theme-change", { detail: { theme } }),
    );
  }

  function toggle() {
    const current =
      document.documentElement.getAttribute("data-theme") || getSaved();
    const next = current === DARK ? LIGHT : DARK;
    apply(next);

    // Clean up all existing toasts to prevent stacking (Anti-freeze & Anti-duplicate)
    document.querySelectorAll(".jaxpay-custom-toast").forEach((el) => {
      if (el.hideTimeout) clearTimeout(el.hideTimeout);
      if (el.removeTimeout) clearTimeout(el.removeTimeout);
      el.remove();
    });

    // Create custom toast
    const toast = document.createElement("div");
    toast.className = "jaxpay-custom-toast";

    // Add icon and text
    const iconClass = next === LIGHT ? "fas fa-sun" : "fas fa-moon";
    const text = next === LIGHT ? "Mode Terang Aktif" : "Mode Gelap Aktif";
    toast.innerHTML = `<div class="toast-icon"><i class="${iconClass}"></i></div><div class="toast-text">${text}</div>`;

    // Append to DOM
    if (document.body) {
      document.body.appendChild(toast);
    }

    // Force DOM reflow so CSS transition works immediately without requestAnimationFrame
    toast.offsetHeight;

    // Show toast with animation
    toast.classList.add("show");

    // Auto close logic attached directly to the DOM element
    toast.hideTimeout = setTimeout(() => {
      toast.classList.remove("show");
      toast.removeTimeout = setTimeout(() => {
        if (toast.parentNode) {
          toast.remove();
        }
      }, 500); // Ensure enough time for the fade-out CSS transition
    }, 2500);
  }

  function init() {
    apply(getSaved());
  }

  return { init, toggle, apply, getSaved, themedValue };
})();

window.ThemeManager = ThemeManager;
ThemeManager.init();

function insertAppLogo() {
  const path = window.location.pathname || "";
  if (!path.includes("/halaman/")) return;
  const phoneContent = document.querySelector(".phone-content");
  if (!phoneContent || document.querySelector(".app-logo-row")) return;

  const logoRow = document.createElement("div");
  logoRow.className = "app-logo-row animate-up";
  logoRow.innerHTML = `
    <img src="../assets/img/Logo.png" alt="JAXPAY" class="app-logo">
    <div class="app-logo-text">
      <div class="app-logo-title">JAXPAY</div>
      <div class="app-logo-sub">School Digital Wallet</div>
    </div>
  `;
  phoneContent.insertBefore(logoRow, phoneContent.firstChild);
}

document.addEventListener("DOMContentLoaded", function () {
  try {
    const path = window.location.pathname || "";
    if (path.includes("/auth/")) {
      document
        .querySelectorAll(
          "[data-theme-toggle], .theme-toggle-mobile, .theme-toggle-admin",
        )
        .forEach((el) => {
          el.style.display = "none";
        });
      ThemeManager.apply(ThemeManager.getSaved());
      return;
    }
  } catch (e) {}

  // Bind toggle to buttons using attribute or common admin/mobile classes
  document
    .querySelectorAll(
      "[data-theme-toggle], .theme-toggle-admin, .theme-toggle-mobile",
    )
    .forEach((btn) => {
      if (btn.dataset.themeBound === "1") return;
      btn.dataset.themeBound = "1";
      btn.addEventListener("click", ThemeManager.toggle);
    });

  ThemeManager.apply(ThemeManager.getSaved());
  insertAppLogo();
});

document.addEventListener("keydown", function (e) {
  if (e.ctrlKey && e.shiftKey && e.key === "T") {
    e.preventDefault();
    ThemeManager.toggle();
  }
});
