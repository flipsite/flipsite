ready(() => {
  document.querySelectorAll('[data-youtube-play]').forEach(el => {
    const container = el.parentNode;
    container.onclick = () => {
      const iframe = el.querySelector('iframe');
      iframe.style.backgroundColor = 'blue';
      iframe.setAttribute('src', iframe.getAttribute('data-youtube-play'));
      iframe.style.pointerEvents = 'auto';
      container.querySelector('svg').remove();
    }
  });
});