ready(()=>{
    document.querySelectorAll('[data-nav]').forEach((nav)=>{
      var active = nav.getAttribute('data-active');
      var notActive = undefined;
      var content = {};
      nav.querySelectorAll('a').forEach((item)=>{
        if (notActive === undefined) {
          var cls = item.getAttribute('class');
          if (cls !== active) {
            notActive = cls;
          }
          var id = item.getAttribute('href').replace('#','');
          content[id] = document.getElementById(id);
        }
        item.onclick = function(e){
          e.preventDefault();
          var isActive = e.target.getAttribute('class') === nav.getAttribute('data-active');
          if (isActive) return;
          nav.querySelectorAll('a').forEach((item)=>{
            item.setAttribute('class',nav.getAttribute('data-not-active'));
          });
          e.target.setAttribute('class',nav.getAttribute('data-active'));
          var active = e.target.getAttribute('href').replace('#','');
          for (var id in content) {
            if (id === active) {
              content[id].classList.remove('hidden');
            } else {
              content[id].classList.add('hidden');}
          }
        }
      });
      nav.setAttribute('data-not-active',notActive);
    });
});