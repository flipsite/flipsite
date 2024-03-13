ready(() => {

  const updateWidths = () => {
    const scrollTop = document.documentElement.scrollTop;
    const scrollHeight = document.documentElement.scrollHeight;
    const clientHeight = document.documentElement.clientHeight;
    const progress = (scrollTop / (scrollHeight - clientHeight)) * 100;
    document.querySelectorAll('[data-scroll-progress-width]').forEach(element => {
      element.style.width = progress + '%';
    });
  }
  updateWidths();
  window.addEventListener('scroll', updateWidths);
});