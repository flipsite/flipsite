ready(() => {
  const elements = document.querySelectorAll('[data-text-scale]');
  if (elements.length === 0) return;
  elements.forEach(element => {
    const rect = element.getBoundingClientRect();
    element.setAttribute('data-width', rect.width);
  });
  const updateScale = () => {
    elements.forEach(element => {
      const rect = element.parentNode.getBoundingClientRect();
      if (rect.width >= element.getAttribute('data-width')) {
        element.style.transform = 'scale(1)';
      } else {
        const scale = 0.94*rect.width/element.getAttribute('data-width');
        element.style.transform = 'scale('+scale+')';
      }
    });
  }
  window.addEventListener('resize', updateScale );
  updateScale();
});