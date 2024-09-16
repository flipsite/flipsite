ready(() => {
  const lazyBackgrounds = document.querySelectorAll("[data-lazybg]");
  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const element = entry.target;
        const bg = element.getAttribute("data-lazybg");
        element.style.backgroundImage = element.getAttribute("data-lazybg");
        element.removeAttribute("data-lazybg");
        observer.unobserve(element);
      }
    });
  }, {
    root: null,
    rootMargin: "0px",
    threshold: 0
  });
  lazyBackgrounds.forEach(element => {
    observer.observe(element);
  });
});
