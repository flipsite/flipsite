
var toggleElements = {};
function toggle(id,prefix) {
    var target = document.getElementById(id);
    if (!target.getAttribute('data-toggle')) {
      toggleElements[id+'-'+prefix] = target.parentNode.querySelectorAll('[class*="'+prefix+'"], [class*="!'+prefix+'"]');
      toggleElements[id+'-'+prefix].forEach((el) => {
        var classes = el.getAttribute('class').split(' ');
        var toggleClasses = [];
        for (var i=0; i<classes.length; i++) {
          if (classes[i].indexOf(prefix+':') !== -1) {
            el.classList.remove(classes[i]);
            var tmp = classes[i].split(':');
            tmp.shift();
            toggleClasses.push(tmp.join(':'));
          }
        }
        el.setAttribute('data-toggle-'+prefix,toggleClasses.join(' '));
      });
      target.setAttribute('data-toggle',1);
    }
    toggleElements[id+'-'+prefix].forEach((el) => {
      el.getAttribute('data-toggle-'+prefix).split(' ').forEach((cls)=>{
        el.classList.toggle(cls)
      });
    });
}
document.body.addEventListener("dummy", function(e) {
  // something that does nothing
});