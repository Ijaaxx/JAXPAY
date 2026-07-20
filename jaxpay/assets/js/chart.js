/**
 * JAXPAY - Chart Defaults & Helpers
 */

if (typeof Chart !== 'undefined') {
  const instances = [];

  function getThemeColors() {
    const s = getComputedStyle(document.documentElement);
    const theme = document.documentElement.getAttribute('data-theme') || 'dark';
    return {
      theme,
      text: s.getPropertyValue('--admin-text').trim() || (theme === 'light' ? '#0f172a' : '#f8fafc'),
      muted: s.getPropertyValue('--admin-muted').trim() || (theme === 'light' ? '#64748b' : '#94a3b8'),
      surface: s.getPropertyValue('--admin-surface').trim() || (theme === 'light' ? '#fff' : '#101a2d'),
      border: s.getPropertyValue('--admin-border').trim() || (theme === 'light' ? 'rgba(15,23,42,.1)' : 'rgba(148,163,184,.16)')
    };
  }

  function applyChartTheme(chart) {
    if (!chart) return;
    try {
      const c = getThemeColors();
      chart.options.plugins = chart.options.plugins || {};
      chart.options.plugins.legend = chart.options.plugins.legend || {};
      chart.options.plugins.legend.labels = chart.options.plugins.legend.labels || {};
      chart.options.plugins.legend.labels.color = c.muted;
      chart.options.plugins.tooltip = {
        ...(chart.options.plugins.tooltip || {}),
        backgroundColor: c.surface,
        titleColor: c.text,
        bodyColor: c.muted,
        borderColor: c.border,
        borderWidth: 1
      };
      if (chart.options.scales) {
        Object.values(chart.options.scales).forEach(scale => {
          scale.ticks = scale.ticks || {};
          scale.grid = scale.grid || {};
          scale.ticks.color = c.muted;
          scale.grid.color = c.border;
        });
      }
      chart.data.datasets.forEach(ds => {
        if (ds.pointBorderColor) ds.pointBorderColor = c.surface;
        if (ds.borderColor && chart.config.type === 'doughnut') ds.borderColor = c.surface;
      });
      chart.update('none');
    } catch (e) {
      console.warn("Failed to apply chart theme dynamically: ", e);
    }
  }

  function applyAllCharts() {
    instances.forEach(applyChartTheme);
  }

  Chart.defaults.color = getThemeColors().muted;
  Chart.defaults.borderColor = getThemeColors().border;
  Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";

  Chart.defaults.plugins.tooltip = {
    ...(Chart.defaults.plugins.tooltip || {}),
    backgroundColor: getThemeColors().surface,
    titleColor: getThemeColors().text,
    bodyColor: getThemeColors().muted,
    borderColor: getThemeColors().border,
    borderWidth: 1,
    cornerRadius: 10,
    padding: 12,
    callbacks: {
      label: ctx => {
        const val = ctx.parsed.y ?? ctx.parsed;
        if (typeof val === 'number' && val > 1000) return ' Rp ' + val.toLocaleString('id-ID');
        return ' ' + val;
      }
    }
  };

  const NativeChart = Chart;
  class WrappedChart extends NativeChart {
    constructor(...args) {
      super(...args);
      instances.push(this);
    }
    destroy() {
      const idx = instances.indexOf(this);
      if (idx !== -1) instances.splice(idx, 1);
      super.destroy();
    }
    update(...args) {
      const c = getThemeColors();
      WrappedChart.defaults.color = c.muted;
      WrappedChart.defaults.borderColor = c.border;
      return super.update(...args);
    }
  }

  window.Chart = WrappedChart;

  window.JaxpayCharts = { applyChartTheme, applyAllCharts, getThemeColors };
  window.addEventListener('jaxpay:theme-change', () => requestAnimationFrame(applyAllCharts));
  document.addEventListener('DOMContentLoaded', () => requestAnimationFrame(applyAllCharts));
}

// Helper: create gradient fill
function createGradient(ctx, color1, color2) {
  const gradient = ctx.createLinearGradient(0, 0, 0, 300);
  gradient.addColorStop(0, color1);
  gradient.addColorStop(1, color2);
  return gradient;
}

// Helper: mini sparkline
function renderSparkline(canvasId, data, color = '#6C3CE1') {
  const canvas = document.getElementById(canvasId);
  if (!canvas || typeof Chart === 'undefined') return;
  new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels: data.map((_, i) => i + 1),
      datasets: [{
        data,
        borderColor: color,
        backgroundColor: 'transparent',
        tension: 0.4,
        pointRadius: 0,
        borderWidth: 2
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { enabled: false } },
      scales: { x: { display: false }, y: { display: false } }
    }
  });
}
