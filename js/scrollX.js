
function scrollX(targetId, direction) {
  const target = document.getElementById(targetId);
  if (!target) return;
  if ('left' === direction) {
    target.scrollLeft -= target.clientWidth
  } else {
    target.scrollLeft += target.clientWidth
  }
}