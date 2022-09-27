ready(() => {
  const observer = new IntersectionObserver(handleIntersection,{
    threshold: 0.0,
  });
  function handleIntersection(entries) {
    entries.map((entry) => {
      if (entry.isIntersecting) {
        var src = entry.target.getAttribute('data-src-onenter');
        entry.target.removeAttribute('data-src-onenter');
        entry.target.setAttribute('src', src);
        observer.unobserve(entry.target)
      }
    });
  }
  var elements = document.querySelectorAll('[data-src-onenter]');
  elements.forEach((el)=>{
    observer.observe(el);
  });
});