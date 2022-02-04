if (localStorage.getItem('remember')) {
    var remember = JSON.parse(localStorage.getItem('remember'));
    console.log(remember)
    window.scroll(0, parseInt(remember.body));
    var i = 0;
    document.querySelectorAll('[data-remember]').forEach((el)=>{
        el.scrollLeft = parseInt(remember.scrolls[i++]);
    });
    localStorage.removeItem('remember');
}

document.querySelectorAll('[data-remember]').forEach((el)=>{
    el.addEventListener('click',() => {
        var remember = {body: window.scrollY, scrolls:[]};
        document.querySelectorAll('[data-remember]').forEach((el)=>{
            remember.scrolls.push(el.scrollLeft);
        });
        localStorage.setItem('remember',JSON.stringify(remember));
    });
});
// document.body.setAttribute('id','body')
// if (localStorage.getItem('remember')) {
//   var remember = JSON.parse(localStorage.getItem('remember'));
//   for (var id in remember) {
//     var el = document.getElementById(id);
//     var text = el.textContent.replace(/[^A-Z0-9]/ig, "")
//     if (text == remember[id].text) {
//       el.scrollLeft = parseInt(remember[id].left);
//       el.scrollTop = parseInt(remember[id].top);
//     }
//     localStorage.removeItem('remember');
//   }
// }

// document.body.setAttribute('data-remember',true)


// window.addEventListener('click', (e) => {
//   console.log(e.target);
//   // document.querySelectorAll('[data-remember]').forEach((el)=>{
//   //   var id = el.getAttribute('id');
//   //   var remember = {};
//   //   if (localStorage.getItem('remember')) {
//   //     remember = JSON.parse(localStorage.getItem('remember'))
//   //   }
//   //   remember[id] = {
//   //     left: el.scrollLeft,
//   //     right: el.scrollTop,
//   //     text: el.textContent.replace(/[^A-Z0-9]/ig, "")
//   //   };
//   //   localStorage.setItem('remember',JSON.stringify(remember));
//   //   alert(JSON.stringify(remember));
//   // });
// });