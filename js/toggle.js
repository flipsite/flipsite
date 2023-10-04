
function toggle(self,force, minScreenWidth) {
  if (minScreenWidth !== undefined && minScreenWidth > window.innerWidth) return;
  const prefix = 'open'
  
  const findParentWithAttributeOrRoot = function(node, attributeName) {
    if (node.hasAttribute(attributeName)) {
      return node;
    }
    while (node) {
      if (node.parentElement.tagName === 'BODY') {
        return node;
      }
      node = node.parentElement;
      if (node.hasAttribute(attributeName)) {
        return node;
      }
    }
  }

  e = window.event;
  e.preventDefault();
  if (self.hasAttribute('aria-expanded')) {
    self.setAttribute('aria-expanded','false' == self.getAttribute('aria-expanded') ? 'true' : 'false');
  }
  const target = findParentWithAttributeOrRoot(self,'data-toggle-target');
  if (target.getAttribute('data-toggle') === null) {
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
    target.setAttribute('data-toggle',0);
  }

  if (force !== undefined) {
    if (parseInt(target.getAttribute('data-toggle')) === 0 && !force) return;
    if (parseInt(target.getAttribute('data-toggle')) === 1 && force) return;
  }
  const toggleClasses = function(el,prefix) {
    let classes = el.getAttribute('data-toggle-'+prefix).split(' ');
    classes.forEach((cls)=>{
      if (cls) {
        el.classList.toggle(cls)
      }
    });
  }

  toggleClasses(target,prefix);
  target.setAttribute('data-toggle',parseInt(target.getAttribute('data-toggle')) === 1 ? 0 : 1);
  target.querySelectorAll('[data-toggle-'+prefix+']').forEach((el)=>{
    const elementTarget = findParentWithAttributeOrRoot(el,'data-toggle-target');
    if (target === elementTarget || elementTarget === el) {
      toggleClasses(el,prefix);
    } 
  });
}