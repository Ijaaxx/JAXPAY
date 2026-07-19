<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAXPAY - Theme Test</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/admin.css">
<link rel="stylesheet" href="assets/css/theme.css?v=4">
<script src="assets/js/theme.js?v=3"></script>
<style>
  .test-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
  }
  .test-section {
    background: var(--admin-surface);
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
  }
  .test-section h2 {
    color: var(--admin-text);
    margin-bottom: 16px;
    font-size: 18px;
  }
  .test-items {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
  }
  .test-item {
    background: var(--admin-surface2);
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    padding: 16px;
    text-align: center;
  }
  .test-item label {
    display: block;
    color: var(--admin-muted);
    font-size: 12px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .test-item input,
  .test-item select,
  .test-item textarea {
    width: 100%;
    background: var(--admin-surface);
    border: 1px solid var(--admin-border);
    border-radius: 6px;
    padding: 8px 12px;
    color: var(--admin-text);
    font-family: inherit;
    margin-bottom: 8px;
  }
  .test-item button {
    width: 100%;
    background: linear-gradient(135deg, #6C3CE1, #9B72EF);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
  }
  .test-item button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108,60,225,0.4);
  }
  .badge {
    display: inline-block;
    background: #EF4444;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
  }
  .badge.success { background: #10B981; }
  .badge.warning { background: #F59E0B; }
  .theme-toggle-test {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 20px;
    background: var(--primary);
    border: none;
    border-radius: 20px;
    color: white;
    cursor: pointer;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    z-index: 1000;
  }
  .theme-toggle-test:hover {
    opacity: 0.9;
  }
</style>
</head>
<body>

<button class="theme-toggle-test" data-theme-toggle onclick="if(window.ThemeManager)ThemeManager.toggle()">
  <i class="fas fa-sun"></i>
  <span id="themeToggleLabelTest">Mode Terang</span>
</button>

<div class="test-container">
  <h1 style="color: var(--admin-text); margin-bottom: 30px;">
    <i class="fas fa-palette"></i> Theme Testing - Dark / Light Mode
  </h1>

  <!-- Text Colors -->
  <div class="test-section">
    <h2>Text Colors</h2>
    <p style="color: var(--admin-text);">Primary Text Color</p>
    <p style="color: var(--admin-muted);">Muted Text Color</p>
    <p style="color: var(--text-primary); margin-top: 12px;">Text Primary (from mobile vars)</p>
  </div>

  <!-- Form Elements -->
  <div class="test-section">
    <h2>Form Elements</h2>
    <div class="test-items">
      <div class="test-item">
        <label>Text Input</label>
        <input type="text" placeholder="Type something...">
      </div>
      <div class="test-item">
        <label>Email Input</label>
        <input type="email" placeholder="email@example.com">
      </div>
      <div class="test-item">
        <label>Select</label>
        <select>
          <option>Option 1</option>
          <option>Option 2</option>
          <option>Option 3</option>
        </select>
      </div>
      <div class="test-item">
        <label>Password</label>
        <input type="password" placeholder="••••••••">
      </div>
      <div class="test-item">
        <label>Button</label>
        <button>Click Me</button>
      </div>
      <div class="test-item">
        <label>Checkbox</label>
        <input type="checkbox"> Check this
      </div>
    </div>
  </div>

  <!-- Cards -->
  <div class="test-section">
    <h2>Card Styles</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
      <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; padding: 16px; text-align: center; color: var(--text-primary);">
        Card Style 1
      </div>
      <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 8px; padding: 16px; text-align: center; color: var(--admin-text);">
        Card Style 2
      </div>
      <div style="background: linear-gradient(135deg, rgba(108,60,225,0.2), rgba(0,212,255,0.1)); border: 1px solid var(--primary); border-radius: 8px; padding: 16px; text-align: center; color: var(--admin-text);">
        Gradient Card
      </div>
    </div>
  </div>

  <!-- Badges -->
  <div class="test-section">
    <h2>Badges</h2>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
      <span class="badge">Default</span>
      <span class="badge success">Success</span>
      <span class="badge warning">Warning</span>
    </div>
  </div>

  <!-- Status -->
  <div class="test-section">
    <h2>Current Theme Status</h2>
    <p>Saved Theme: <strong id="savedTheme">Loading...</strong></p>
    <p>Current Theme: <strong id="currentTheme">Loading...</strong></p>
  </div>
</div>

<script>
// Update theme labels and status
function updateThemeDisplay() {
  const saved = window.ThemeManager ? ThemeManager.getSaved() : 'dark';
  const current = document.documentElement.getAttribute('data-theme') || 'dark';
  
  document.getElementById('savedTheme').textContent = saved === 'dark' ? '🌙 Dark' : '☀️ Light';
  document.getElementById('currentTheme').textContent = current === 'dark' ? '🌙 Dark' : '☀️ Light';
  
  const lbl = document.getElementById('themeToggleLabelTest');
  const icon = document.querySelector('.theme-toggle-test i');
  if (lbl) lbl.textContent = saved === 'dark' ? 'Mode Terang' : 'Mode Gelap';
  if (icon) icon.className = saved === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

document.addEventListener('DOMContentLoaded', function() {
  updateThemeDisplay();
  
  // Update every time theme changes
  if (window.ThemeManager) {
    const origToggle = ThemeManager.toggle;
    ThemeManager.toggle = function() {
      origToggle.call(ThemeManager);
      updateThemeDisplay();
    };
  }
});
</script>

</body>
</html>
