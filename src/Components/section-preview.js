ready(function(){
  const sections = document.querySelectorAll('[data-contain]');
  sections.forEach(function(el) {
    el.style.transformOrigin = 'top left';
  });
  function windowSizeChange() {
    var maxHeight = 0;
    sections.forEach(function(el) {
      el.parentNode.parentNode.style.height = 'auto'
      var w = 1280;
      el.style.width = w+'px';
      var scale = el.parentNode.offsetWidth / w;
      el.style.transform = 'scale('+(scale*100.0)+'%)';
      var height = scale*el.offsetHeight;
      el.parentNode.style.height = height+'px'
      var boxHeight = el.parentNode.parentNode.offsetHeight;
      if (boxHeight > maxHeight) {
        maxHeight = boxHeight;
      }
    });
    maxHeight+=window.innerWidth/100*5
    if (maxHeight<160)maxHeight=160
    sections.forEach(function(el) {
      el.parentNode.parentNode.style.height = maxHeight+'px'
      var box = el.parentNode;
      var offsetY = (maxHeight - box.offsetHeight)/2;
      box.style.transform = 'translateY('+offsetY+'px)';




    });
  }
  window.onresize = windowSizeChange;
  windowSizeChange();
});