ready(() => {
  const observer = new IntersectionObserver(([entry]) => {
      var toggleStuckClasses = function(el,add) {
        el.getAttribute('data-stuck').split(' ').forEach(cls => {
          if (add) {
            el.classList.add(cls);
          } else {
            el.classList.remove(cls);
          }
        });
      }
      var isStuck = entry.intersectionRatio < 1.0;
      if (entry.target.getAttribute('data-stuck')) {
        toggleStuckClasses(entry.target,isStuck);
      }
      entry.target.querySelectorAll('[data-stuck]').forEach(el=>{
        toggleStuckClasses(el,isStuck);
      });
  }, {threshold: [1.0]});
  var elements = document.querySelectorAll('.sticky.top-0').forEach(el=> {
    var parseStuckClasses = function(stuck) {
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
    };
    el.style.top = '-1px';
    observer.observe(el);
    parseStuckClasses(el);
    el.querySelectorAll('[class*="stuck:"]').forEach((stuck)=>{
      parseStuckClasses(stuck);
    });
  });
});
