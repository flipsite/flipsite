const runTextScale = (el) => {
  if (!el || !el.parentNode) return;

  // Use stored base font size if available, or compute and store it
  let baseFontSize = parseFloat(el.dataset.textScaleBase);
  if (!baseFontSize) {
    baseFontSize = parseFloat(window.getComputedStyle(el).fontSize) || 16;
    el.dataset.textScaleBase = baseFontSize;
  }

  const parent = el.parentNode;
  const parentStyle = window.getComputedStyle(parent);
  const maxWidth =
    parent.clientWidth -
    parseFloat(parentStyle.paddingLeft) -
    parseFloat(parentStyle.paddingRight);

  // Early exit if no space available
  if (maxWidth <= 0) return;

  const getFontSize = (element, maxWidth) => {
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
    });
    document.body.appendChild(offscreen);

    // Binary search for optimal fit — only shrink (never exceed base size)
    let min = 6;
    let max = baseFontSize;
    let optimalSize = min;
    const tolerance = 0.5;

    while (max - min > tolerance) {
      const fontSize = (min + max) / 2;
      offscreen.style.fontSize = fontSize + 'px';
      const width = offscreen.offsetWidth;

      if (width <= maxWidth) {
        optimalSize = fontSize;
        min = fontSize;
      } else {
        max = fontSize;
      }
    }

    offscreen.remove();
    return Math.max(Math.min(optimalSize, baseFontSize), 6) + 'px';
  };

  el.style.fontSize = getFontSize(el, maxWidth);
};


ready(() => {
  const elements = new Set();
  const runAll = () => elements.forEach(runTextScale);
  
  // Debounce resize callback to avoid excessive recalculations
  const createDebouncedCallback = (el) => {
    let rafId = null;
    return () => {
      if (rafId) return;
      rafId = requestAnimationFrame(() => {
        runTextScale(el);
        rafId = null;
      });
    };
  };

  // Initialize and observe all matching elements
  document.querySelectorAll('[data-text-scale]').forEach((el) => {
    runTextScale(el);
    elements.add(el);

    const debouncedCallback = createDebouncedCallback(el);
    const observer = new ResizeObserver(debouncedCallback);
    observer.observe(el);
    if (el.parentNode) {
      observer.observe(el.parentNode);
    }
  });

  // Re-run when fonts finish loading
  if (document.fonts) {
    document.fonts.ready.then(runAll).catch(() => {});
  }

  // Debounce window resize
  let resizeRafId = null;
  window.addEventListener('resize', () => {
    if (resizeRafId) return;
    resizeRafId = requestAnimationFrame(() => {
      runAll();
      resizeRafId = null;
    });
  });
});