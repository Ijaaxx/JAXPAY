/**
 * JAXPAY - Safe Chart Helpers
 */
if (typeof Chart !== 'undefined') {
  window.JaxpayCharts = {
    applyChartTheme: function() {},
    applyAllCharts: function() {},
    getThemeColors: function() {
      return {
        theme: 'light',
        text: '#0f172a',
        muted: '#64748b',
        surface: '#fff',
        border: 'rgba(15,23,42,.1)'
      };
    }
  };
}
