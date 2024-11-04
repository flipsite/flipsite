
function toggle(self,force,minScreenWidth) {
  if (minScreenWidth !== undefined && minScreenWidth > window.innerWidth) return;
  const findParentWithAttributeOrRoot = function(node, attributeName) {
    if (node.hasAttribute(attributeName)) {
      return node;
    }
    while (node && node.parentElement.tagName !== 'BODY') {
      node = node.parentElement;
      if (node.hasAttribute(attributeName)) {
        return node;
      }
    }
    return node;
  }
  self.setAttribute('aria-expanded','false' == self.getAttribute('aria-expanded') ? 'true' : 'false');
  const target = findParentWithAttributeOrRoot(self,'data-toggle-target');
  if (target.getAttribute('data-toggle-state') === null) {
    target.setAttribute('data-toggle-state',0);
  }
  if (force !== undefined) {
    if (parseInt(target.getAttribute('data-toggle-state')) === 0 && !force) return;
    if (parseInt(target.getAttribute('data-toggle-state')) === 1 && force) return;
  }
  const toggleClasses = function(el) {
    if (el.hasAttribute('data-toggle')) {
      el.getAttribute('data-toggle').split(' ').forEach((cls)=>{
        el.classList.toggle(cls)
      });
    }
  }
  toggleClasses(target);
  target.setAttribute('data-toggle-state',parseInt(target.getAttribute('data-toggle-state')) === 1 ? 0 : 1);
  target.querySelectorAll('[data-toggle]').forEach((el)=>{
    const elementTarget = findParentWithAttributeOrRoot(el,'data-toggle-target');
    if (target === elementTarget || elementTarget === el) {
      toggleClasses(el);
    } 
  });
}