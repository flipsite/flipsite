ready(() => {
  function handleIntersection(entries) {
    entries.map((entry) => {
      if (entry.isIntersecting) {
        var remove = [];
        for (var i=0; i<entry.target.classList.length; i++) {
          var cls = entry.target.classList[i];
          if (cls.indexOf('enter:') === 0) {
            remove.push(cls)
          }
        }
        for (var i=0; i<remove.length; i++) {
          entry.target.classList.remove(remove[i])
        }
      }
    });
  }
  const observer = new IntersectionObserver(handleIntersection,{
    threshold: 0.1,
  });
  var elements = document.querySelectorAll('[class*="enter:"]');
  elements.forEach((el)=>{
    observer.observe(el);
  });
});