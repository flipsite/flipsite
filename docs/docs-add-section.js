ready(function(){
  function createLinks(pages) {
    var link = document.querySelector("a[href='#before']");
    var buttons = link.parentNode;
    link.remove();
    for (var i=0; i<pages.length; i++) {
      var pageLink = link.cloneNode(true);
      pageLink.innerHTML = pages[i];
      pageLink.removeAttribute('href');
      pageLink.onclick = function(e) {
        postData(e.target.innerHTML);
        e.target.remove();
      }
      buttons.appendChild(pageLink);
    }
  }
  function postData(page) {
    var json = document.querySelectorAll('div[data-type=json]');
    var section = json[0].innerHTML;
    var style = json[1].innerHTML;
    fetch('/api/sections/'+page.replaceAll('/','-'), {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      method: 'POST',
      body: section
    })
    .then(function(res){ console.log(res) })
    .catch(function(res){ console.log(res) })

    parsedStyle = JSON.parse(style);
    for (var id in parsedStyle) {
      fetch('/api/theme/style/'+id, {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        method: 'POST',
        body: JSON.stringify(parsedStyle[id])
      })
      .then(function(res){ console.log(res) })
      .catch(function(res){ console.log(res) })
    }
  }
  const load = async () => {
    const response = await fetch('/api/pages');
    const pages = await response.json();
    pages.unshift('after')
    pages.unshift('before');
    createLinks(pages);
  }
  load();
})