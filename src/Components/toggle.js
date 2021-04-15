function toggle(el) {
  var toggleOnOff = function(el) {
    if (el.dataset.onoff === undefined) {
      var off = [];
      var on = [];
      for (var i=0; i<el.classList.length; i++) {
        if (el.classList[i].search('off:') === 0) {
          off.push(el.classList[i]);
        }
        if (el.classList[i].search('on:') === 0) {
          on.push(el.classList[i]);
        }
      }
      el.dataset.onoff = off.join(' ');
      for (var i=0; i<on.length; i++) {
        el.classList.remove(on[i])
        on[i] = on[i].replace('on:','');
      }
      el.dataset.onoff+= ' '+on.join(' ');
    }
    var classes = el.dataset.onoff.split(' ');
    for (var i=0; i<classes.length; i++) {
      if (classes[i]) el.classList.toggle(classes[i])
    }
  }
  if (el.classList.contains('toggle')) toggleOnOff(el);
  el.querySelectorAll('.toggle').forEach(el => {toggleOnOff(el)})
}
