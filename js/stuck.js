ready(() => {
  document.querySelectorAll('[data-stuck]').forEach((stickyEl) => {
    const stuckClasses = stickyEl.getAttribute('data-stuck').split(' ');
    const style = getComputedStyle(stickyEl);
    const offsetTop = parseInt(style.top)
    
    // Create a sentinel just before the sticky element
    const sentinel = document.createElement('div');
    sentinel.style.position = 'absolute';
    sentinel.style.height = '1px';
    sentinel.style.width = '100%';
    sentinel.style.background = 'pink'
    stickyEl.parentNode.insertBefore(sentinel, stickyEl);
    let isStuck = false;
    
    const observer = new IntersectionObserver(
      ([entry]) => {
        const isStuckNow = entry.boundingClientRect.y <= offsetTop;
        console.log('STUCK NOW',isStuckNow)
        if (isStuckNow === isStuck) return;
        isStuck = isStuckNow;
        stuckClasses.forEach(cls => {
          console.log(cls)
          stickyEl.classList.toggle(cls);
        });
      },
      { threshold: [1] }
    );
  
    observer.observe(sentinel);
  });
  
});