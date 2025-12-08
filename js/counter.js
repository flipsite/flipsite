const counters = [];

const runCounter = (el) => {
  const valEl = el.querySelector("span");
  const from = parseFloat(el.dataset.from) || 0;
  const to = parseFloat(el.dataset.to) || 0;
  const duration = parseInt(el.dataset.duration, 10) || 2000;
  const timing = el.dataset.timing || "ease-in-out";
  const thousands = el.dataset.thousands || "none";
  const decimals = parseInt(el.dataset.decimals, 10) || 0;
  const decimalSeparator = el.dataset.decimalSeparator || "period";
  const start = performance.now();

  // --- Helpers (private to runCounter) ---
  const easing = {
    linear: (x) => x,
    "ease-in": (x) => 1 - Math.cos((x * Math.PI) / 2),
    "ease-out": (x) => Math.sin((x * Math.PI) / 2),
    "ease-in-out": (x) => -(Math.cos(Math.PI * x) - 1) / 2,
  };

  const separators = {
    none: "",
    space: "&nbsp;",
    comma: ",",
    period: ".",
    apostrophe: "'"
  };

  const formatNumber = (num, decimals, decimalSep, thousandsSep) => {
    const parts = num.toFixed(decimals).split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
    return parts.join(decimalSep);
  };

  // --- Animation loop ---
  const step = (now) => {
    const progress = Math.min((now - start) / duration, 1);
    const ease = easing[timing] ? easing[timing](progress) : easing.linear(progress);
    const currentValue = (to - from) * ease + from;

    valEl.innerHTML = formatNumber(
      currentValue,
      decimals,
      separators[decimalSeparator],
      separators[thousands]
    );

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