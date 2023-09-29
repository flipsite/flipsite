
function toggle(self,prefix) {
  prefix = prefix || 'open'
  
  const findParentWithAttributeOrRoot = function(node, attributeName) {
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
  
  const target = findParentWithAttributeOrRoot(self,'data-toggle-target')

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

  const toggleClasses = function(el,prefix) {
    let classes = el.getAttribute('data-toggle-'+prefix).split(' ');
    classes.forEach((cls)=>{
      if (cls) {
        el.classList.toggle(cls)
      }
    });
  }

  toggleClasses(target,prefix);
  target.querySelectorAll('[data-toggle-'+prefix+']').forEach((el)=>{
    const elementTarget = findParentWithAttributeOrRoot(el,'data-toggle-target');
    if (target === elementTarget) {
      toggleClasses(el,prefix);
    }
  });
}