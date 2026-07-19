/**
 * JAXPAY - Admin JS Utilities
 */

// Auto-refresh dashboard every 60 seconds
if (window.location.pathname.includes('dashboard.php')) {
  setInterval(() => {
    fetch('dashboard.php', { method: 'HEAD' })
      .then(() => {
        const badge = document.querySelector('.topbar-btn .badge');
        if (badge) badge.style.animation = 'pulse 0.5s ease';
      });
  }, 60000);
}

// Confirm delete helper
function confirmAction(message, onConfirm, icon = 'warning') {
  Swal.fire({
    title: 'Konfirmasi',
    text: message,
    icon,
    showCancelButton: true,
    confirmButtonText: 'Ya, Lanjutkan',
    cancelButtonText: 'Batal',
    background: getThemeValue('--swal-bg', '#16162A'), color: getThemeValue('--swal-text', '#fff'),
    confirmButtonColor: '#6C3CE1', cancelButtonColor: '#374151'
  }).then(r => { if (r.isConfirmed && typeof onConfirm === 'function') onConfirm(); });
}

function getThemeValue(name, fallback) {
  return getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
}

function themedSwalOptions(options = {}) {
  return {
    background: getThemeValue('--swal-bg', '#16162A'),
    color: getThemeValue('--swal-text', '#fff'),
    confirmButtonColor: '#6C3CE1',
    cancelButtonColor: '#64748B',
    ...options
  };
}

// Format number as Rupiah
function formatRupiah(n) {
  return 'Rp ' + parseInt(n).toLocaleString('id-ID');
}

// Toast admin
function adminToast(msg, icon = 'success') {
  Swal.fire(themedSwalOptions({ toast:true, position:'top-end', icon, title:msg, timer:2500, showConfirmButton:false }));
}

window.getThemeValue = getThemeValue;
window.themedSwalOptions = themedSwalOptions;

// Sidebar close on mobile when clicking overlay
document.addEventListener('click', e => {
  const sidebar = document.getElementById('adminSidebar');
  const toggle  = document.getElementById('sidebarToggle');
  if (!sidebar || !toggle) return;
  if (window.innerWidth <= 900 && sidebar.classList.contains('open')) {
    if (!sidebar.contains(e.target) && e.target !== toggle) {
      sidebar.classList.remove('open');
    }
  }
});

// Table row hover effect enhancement
document.querySelectorAll('tbody tr').forEach(row => {
  row.style.cursor = 'default';
});

// Auto-close alerts
document.querySelectorAll('.alert-auto-close').forEach(el => {
  setTimeout(() => {
    el.style.transition = 'opacity 0.5s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  }, 4000);
});
