
var toggleElements = {};
function toggle(self,prefix) {
    prefix = prefix || 'open'
    var getParents = function (elem) {
      let parents = [];
      for ( ; elem && elem.tagName !== 'BODY'; elem = elem.parentNode ) {
        parents.push(elem);
      }
      return parents;
    };
    e = window.event;
    e.preventDefault();
    if (self.hasAttribute('aria-expanded')) {
      self.setAttribute('aria-expanded','false' == self.getAttribute('aria-expanded') ? 'true' : 'false');
    }
    
    let target = null;
    let parents = getParents(self);
    while (!target) {
      const p = parents.shift();
      if (p.hasAttribute('data-toggle-target')) {
        target = p;
        continue;
      }
      if (parents.length === 0) {
        target = p;
      }
    }
    if (!target.getAttribute('data-toggle')) {
      let elements = [target];
      target.querySelectorAll('[class*="'+prefix+'"], [class*="!'+prefix+'"]').forEach((el)=>{
        elements.push(el);
      });
      elements.forEach(el => {
        const classes = el.getAttribute('class').split(' ');
        let toggleClasses = [];
        for (var i=0; i<classes.length; i++) {
          if (classes[i].indexOf(prefix+':') !== -1) {
            el.classList.remove(classes[i]);
            let tmp = classes[i].split(':');
            tmp.shift();
            toggleClasses.push(tmp.join(':'));
          }
        }
        el.setAttribute('data-toggle-'+prefix,toggleClasses.join(' '));
      });
      target.setAttribute('data-toggle',1);
    }
    const toggle = function(el,prefix) {
      let classes = el.getAttribute('data-toggle-'+prefix).split(' ');
      classes.forEach((cls)=>{
        if (cls) {
          el.classList.toggle(cls)
        }
      });
    }
    toggle(target,prefix);
    target.querySelectorAll('[data-toggle-'+prefix+']').forEach((el)=>{
      toggle(el,prefix);
    });
    
}
