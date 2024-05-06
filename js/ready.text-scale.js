ready(() => {
  const elements = document.querySelectorAll('[data-text-scale]');
  if (elements.length === 0) return;
  const getTextWidth = function(element) {
    const offscreen = element.cloneNode(true)
    offscreen.style.transform = 'scale(1)';
    offscreen.style.width = 'auto';
    offscreen.style.height = 'auto';
    offscreen.style.display = 'inline-block';
    document.body.appendChild(offscreen);
    const width = offscreen.offsetWidth;
    document.body.removeChild(offscreen);
    return width;
  }
  const updateScale = () => {
    elements.forEach(element => {
      element.style.transform = 'scale(1)';
      const parentRect = element.parentNode.getBoundingClientRect();
      const parentStyle = window.getComputedStyle(element.parentNode);
      const maxWidth = parentRect.width - parseFloat(parentStyle.paddingLeft) - parseFloat(parentStyle.paddingRight);
      const textWidth = getTextWidth(element);
      const scale = maxWidth / textWidth;
      if (scale < 1) {
        element.style.transform = 'scale('+scale+')';
      }
    });
  }
  window.addEventListener('resize', updateScale );
  document.fonts.ready.then(() => {
    updateScale();
  });
  updateScale();
});