ready(() => {
  function throttle(fn, wait) {
    let lastCall = 0;
    return function (...args) {
      const now = Date.now();
      if (now - lastCall >= wait) {
        lastCall = now;
        fn(...args);
      }
    };
  }

  const stickyElements = [];
  
  document.querySelectorAll('[data-stuck]').forEach((el) => {
    const style = window.getComputedStyle(el);
    const top = parseInt(style.top, 10) || 0;
    const stuckClasses = el.getAttribute('data-stuck').split(' ');
    stickyElements.push({ el, isStuck:el.getBoundingClientRect().top < top, top, stuckClasses });
  });
  
  function determineStickyState() {
    stickyElements.forEach((stickyElement) => {
      const isStuck = stickyElement.el.getBoundingClientRect().top <= stickyElement.top;
      if (stickyElement.isStuck === isStuck) return;
      stickyElement.isStuck = isStuck;
      stickyElement.stuckClasses.forEach(cls => {
        stickyElement.el.classList.toggle(cls);
      });
    });
  }
  
  window.addEventListener('scroll', throttle(determineStickyState, 100));

});