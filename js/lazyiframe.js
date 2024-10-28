ready(() => {
  const lazyIframes = document.querySelectorAll("[data-lazyiframe]");
  if (!lazyIframes.length) {
    return;
  }
  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const element = entry.target;
        const src = element.getAttribute("data-src");
        element.setAttribute('src',src)
        element.removeAttribute("data-src");
        observer.unobserve(element);
      }
    });
  }, {
    root: null,
    rootMargin: "0px",
    threshold: 0
  });
  lazyIframes.forEach(element => {
    const src = element.getAttribute("data-lazyiframe");
    element.removeAttribute("data-lazyiframe");
    element.setAttribute("data-src", src);
    observer.observe(element);
  });
});
