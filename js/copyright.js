ready(() => {
  const y = new Date().getFullYear();
  document.querySelectorAll('[data-copyright]').forEach(e => {
      e.innerHTML = y;
      e.removeAttribute('data-copyright')
  });
});