ready(() => {
  document.querySelectorAll('[data-youtube-play]').forEach(el => {
    el.parentNode.onclick = (e) => {
      const iframe = e.target.querySelector('iframe');
      iframe.setAttribute('src', el.getAttribute('data-youtube-play') ?? '');
      iframe.style.pointerEvents = 'auto';
      e.target.querySelector('svg').remove();
      e.target.querySelector('img').remove();
    }
  });
});