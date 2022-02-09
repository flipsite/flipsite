ready(() => {
  const observer = new IntersectionObserver(([entry]) => {
      entry.target.querySelectorAll('[data-stuck]').forEach(el=>{
        el.getAttribute('data-stuck').split(' ').forEach(cls => {
          if (entry.intersectionRatio < 1) {
            el.classList.add(cls);
          } else {
            el.classList.remove(cls);
          }
        });
      });
  }, {threshold: [1]});
  var elements = document.querySelectorAll('.sticky.top-0').forEach(el=> {
    el.style.top = '-1px';
    observer.observe(el);
    el.querySelectorAll('[class*="stuck:"]').forEach((stuck)=>{
      var classes = stuck.getAttribute('class').split(' ');
      var stuckClasses = [];
      for (var i=0; i<classes.length; i++) {
        if (classes[i].indexOf('stuck:') !== -1) {
          stuck.classList.remove(classes[i]);
          var tmp = classes[i].split(':');
          tmp.shift();
          stuckClasses.push(tmp.join(':'));
        }
      }
      stuck.setAttribute('data-stuck',stuckClasses.join(' '));
    });
  });
});
