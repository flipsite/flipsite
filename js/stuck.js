ready(() => {
  function throttle(fn, wait) {
    let lastCall = 0;
    let timeoutId = null;
    let lastArgs = null;
  
    return function (...args) {
      const now = Date.now();
      const remaining = wait - (now - lastCall);
      lastArgs = args;
  
      if (remaining <= 0) {
        clearTimeout(timeoutId);
        timeoutId = null;
        lastCall = now;
        fn(...args);
      } else if (!timeoutId) {
        timeoutId = setTimeout(() => {
          lastCall = Date.now();
          timeoutId = null;
          fn(...lastArgs);
        }, remaining);
      }
    };
  }
  
  const stickyElements = [];
  
  document.querySelectorAll('[data-stuck]').forEach((el) => {
    const style = window.getComputedStyle(el);
    const top = parseInt(style.top, 10) || 0;
    const stuckClasses = el.getAttribute('data-stuck').split(' ');
    stickyElements.push({ el, top, stuckClasses, isStuck: false });
  });
  
  function determineStickyState() {
    stickyElements.forEach((item) => {
      const rectTop = item.el.getBoundingClientRect().top;
      let isNowStuck;
  
      if (Math.round(rectTop) === 0 && item.top === 0) {
        // Special case for sticky top: 0 elements pinned to viewport
        isNowStuck = window.scrollY > 0;
      } else {
        isNowStuck = Math.round(rectTop) <= item.top;
      }
  
      if (item.isStuck === isNowStuck) return;
  
      item.isStuck = isNowStuck;
      item.stuckClasses.forEach(cls => {
        item.el.classList.toggle(cls, isNowStuck);
      });
    });
  }
  
  window.addEventListener('scroll', throttle(determineStickyState, 100));
  determineStickyState(); // initial check
});