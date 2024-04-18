ready(() => {
  const elements = document.querySelectorAll('[data-text-scale]');
  if (elements.length === 0) return;
  const updateScale = () => {
    elements.forEach(element => {
      element.style.transform = 'scale(1)';
      const rect = element.getBoundingClientRect();
      const parentRect = element.getBoundingClientRect();
      const parentStyle = element.getComputedStyle();
      const maxWidth = parentRect.width - parseFloat(parentStyle.paddingLeft) - parseFloat(parentStyle.paddingRight);
      const scale = maxWidth / rect.width;
      if (scale < 1) {
        element.style.transform = 'scale('+scale+')';
      }
    });
  }
  window.addEventListener('resize', updateScale );
  updateScale();
});