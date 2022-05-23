function setState(target,state) {
  e = window.event;
  e.preventDefault();
  var nav = e.target.parentNode;
  var notActiveClasses = nav.getAttribute('data-not-active').split(' ');
  var activeClasses = nav.getAttribute('data-active').split(' ');
  nav.childNodes.forEach(el=>{
    if ('A' === el.tagName) {
      for (var i=0; i<notActiveClasses.length; i++) el.classList.add(notActiveClasses[i]);
      for (var i=0; i<activeClasses.length; i++) el.classList.remove(activeClasses[i]);
    }
  });
  for (var i=0; i<notActiveClasses.length; i++) e.target.classList.remove(notActiveClasses[i]);
  for (var i=0; i<activeClasses.length; i++) e.target.classList.add(activeClasses[i]);
  
  document.querySelectorAll('#'+target+' [data-state]').forEach(el=>{
    if (!el.hasAttribute('data-state-on')) {
      let on = [], off = [];
      el.getAttribute('class').split(' ').forEach(cls=>{
        if (cls.startsWith('on:')) { on.push(cls.replace('on:','')); }
        if (cls.startsWith('off:')) { off.push(cls.replace('off:','')); }
      });
      el.setAttribute('data-state-on',on.join(' '));
      el.setAttribute('data-state-off',off.join(' '));
    }
    if (state === el.getAttribute('data-state')) {
      el.getAttribute('data-state-off').split(' ').forEach(cls=>{ el.classList.remove(cls)})
      el.getAttribute('data-state-on').split(' ').forEach(cls=>{ el.classList.add(cls)})
    } else {
      el.getAttribute('data-state-on').split(' ').forEach(cls=>{ el.classList.remove(cls)})
      el.getAttribute('data-state-off').split(' ').forEach(cls=>{ el.classList.add(cls)})
    }
  });
}