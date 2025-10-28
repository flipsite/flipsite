const runTextScale = (el) => {
  if (!el || !el.parentNode) return;

  // Use stored base font size if available, or compute and store it
  let baseFontSize = parseFloat(el.dataset.textScaleBase);
  if (!baseFontSize) {
    baseFontSize = parseFloat(window.getComputedStyle(el).fontSize) || 16;
    el.dataset.textScaleBase = baseFontSize; // remember it for future runs
  }

  const getFontSize = (element, maxWidth) => {
    let fontSize = baseFontSize;

    // Create an offscreen clone for measurement
    const offscreen = element.cloneNode(true);
    Object.assign(offscreen.style, {
      position: 'absolute',
      visibility: 'hidden',
      whiteSpace: 'nowrap',
      left: '-9999px',
      top: '0',
      display: 'inline-block',
      width: 'auto',
      height: 'auto',
      fontSize: fontSize + 'px',
    });
    document.body.appendChild(offscreen);

    const textLength = element.textContent.trim().length || 1;

    // Binary search for optimal fit — only shrink (never exceed base size)
    let min = 6;
    let max = baseFontSize;
    let attempts = 0;

    while (attempts < 20) {
      fontSize = (min + max) / 2;
      offscreen.style.fontSize = fontSize + 'px';
      const width = offscreen.offsetWidth;

      if (width > maxWidth) {
        max = fontSize;
      } else {
        min = fontSize;
      }

      if (Math.abs(width - maxWidth) < 0.5) break;
      attempts++;
    }

    offscreen.remove();
    // Clamp font size between 6px and baseFontSize
    return Math.max(Math.min(fontSize, baseFontSize), 6) + 'px';
  };

  const parent = el.parentNode;
  const parentStyle = window.getComputedStyle(parent);
  const maxWidth =
    parent.clientWidth -
    parseFloat(parentStyle.paddingLeft) -
    parseFloat(parentStyle.paddingRight);

  el.style.fontSize = getFontSize(el, maxWidth);
};


ready(() => {
  const elements = new Set();
  const runAll = () => elements.forEach(runTextScale);

  // Initialize and observe all matching elements
  document.querySelectorAll('[data-text-scale]').forEach((el) => {
    runTextScale(el);
    elements.add(el);

    const observer = new ResizeObserver(() => runTextScale(el));
    observer.observe(el);
    observer.observe(el.parentNode);
  });

  // Re-run when fonts finish loading
  if (document.fonts) {
    document.fonts.ready.then(runAll).catch(() => {});
  }

  // Also re-run on window resize
  window.addEventListener('resize', () => {
    requestAnimationFrame(runAll);
  });
});