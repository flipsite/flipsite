ready(() => {
  const lazyBackgrounds = document.querySelectorAll("[data-lazybg]");
  if (!lazyBackgrounds.length) {
    return;
  }
  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const element = entry.target;
        const bg = element.getAttribute("data-bg");
        element.style.backgroundImage = element.getAttribute("data-bg");
        element.removeAttribute("data-bg");
        observer.unobserve(element);
      }
    });
  }, {
    root: null,
    rootMargin: "0px",
    threshold: 0
  });
  lazyBackgrounds.forEach(element => {
    const bg = element.getAttribute("data-lazybg");
    element.removeAttribute("data-lazybg");
    element.setAttribute("data-bg", bg);
    observer.observe(element);
  });
});
