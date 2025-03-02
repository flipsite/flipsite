
function scrollX(element,direction) {
  const findScrollTarget = function(element) {
    let ancestor = element.closest('body > *');
    if (!ancestor) return null;  
    return ancestor.querySelector('[data-dots-target]') || null;
  };
  const target = findScrollTarget(element)
  if (!target) return;
  if ('left' === direction) {
    target.scrollLeft -= target.clientWidth
  } else {
    target.scrollLeft += target.clientWidth
  }
}