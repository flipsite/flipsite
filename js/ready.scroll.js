ready(() => {
  document.addEventListener('scroll', function(e) {
    var root = document.getElementsByTagName( 'html' )[0];
    if (window.scrollY === 0) {
      root.classList.remove('scroll');
    } else if (!root.classList.contains('scroll')) {
      root.classList.add('scroll');
    }
  });
});