ready(() => {
    if (localStorage.getItem('remember')) {
        var remember = JSON.parse(localStorage.getItem('remember'));
        window.scroll({
            top: parseInt(remember.body),
            left:0,
            behavior: 'instant',
        });
        var i = 0;
        document.querySelectorAll('[data-remember]').forEach((el)=>{
            el.scrollLeft = parseInt(remember.scrolls[i++]);
        });
        localStorage.removeItem('remember');
    }
});
function remember(el) {
    var remember = {body: window.scrollY, scrolls:[]};
    document.querySelectorAll('[data-remember]').forEach((el)=>{
        remember.scrolls.push(el.scrollLeft);
    });
    localStorage.setItem('remember',JSON.stringify(remember));
}