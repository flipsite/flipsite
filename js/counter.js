const counters = [];

const runCounter = (el) => {
  const valEl = el.querySelector("span");
  const from = parseInt(el.dataset.from, 10) || 0;
  const to = parseInt(el.dataset.to, 10) || 0;
  const duration = parseInt(el.dataset.duration, 10) || 2000;
  const timing = el.dataset.timing || "ease-in-out";
  const thousands = el.dataset.thousands || "none";
  const start = performance.now();

  // --- Helpers (private to runCounter) ---
  const easing = {
    linear: (x) => x,
    "ease-in": (x) => 1 - Math.cos((x * Math.PI) / 2),
    "ease-out": (x) => Math.sin((x * Math.PI) / 2),
    "ease-in-out": (x) => -(Math.cos(Math.PI * x) - 1) / 2,
  };

  const formatNumber = (num, style) => {
    switch (style) {
      case "space":  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "&nbsp;");
      case "comma":  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      case "period": return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      default:       return num;
    }
  };

  // --- Animation loop ---
  const step = (now) => {
    const progress = Math.min((now - start) / duration, 1);
    const ease = easing[timing] ? easing[timing](progress) : easing.linear(progress);
    const currentValue = Math.round((to - from) * ease + from);

    valEl.innerHTML = formatNumber(currentValue, thousands);

    if (progress < 1) {
      requestAnimationFrame(step);
    }
  };

  requestAnimationFrame(step);
};

// =====================
// Observer setup
// =====================
ready(() => {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const el = entry.target;
        observer.unobserve(el); // only run once
        runCounter(el);
      }
    });
  }, {
    threshold: 0.1,
  });

  document.querySelectorAll("[data-counter]").forEach((el) => {
    counters.push(el);
    observer.observe(el);
  });
});