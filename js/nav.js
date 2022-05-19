function navSelect(id) {
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


  var container = document.getElementById(id).parentNode;
  container.childNodes.forEach(el => {
    if (el.classList !== undefined ) {
      el.classList.remove('block');
      el.classList.add('hidden');
    }
  });
  document.getElementById(id).classList.remove('hidden')
  document.getElementById(id).classList.add('block')
}
// ready(()=>{
//     document.querySelectorAll('[data-nav]').forEach((nav)=>{
//       var active = nav.getAttribute('data-active');
//       var notActive = undefined;
//       var content = {};
//       nav.querySelectorAll('a').forEach((item)=>{
//         if (notActive === undefined) {
//           var cls = item.getAttribute('class');
//           if (cls !== active) {
//             notActive = cls;
//           }
//           var id = item.getAttribute('href').replace('#','');
//           content[id] = document.getElementById(id);
//         }
//         item.onclick = function(e){
//           e.preventDefault();
//           var isActive = e.target.getAttribute('class') === nav.getAttribute('data-active');
//           if (isActive) return;
//           nav.querySelectorAll('a').forEach((item)=>{
//             item.setAttribute('class',nav.getAttribute('data-not-active'));
//           });
//           e.target.setAttribute('class',nav.getAttribute('data-active'));
//           var active = e.target.getAttribute('href').replace('#','');
//           for (var id in content) {
//             if (id === active) {
//               content[id].classList.remove('hidden');
//             } else {
//               content[id].classList.add('hidden');}
//           }
//         }
//       });
//       nav.setAttribute('data-not-active',notActive);
//     });
// });