ready(() => {
    if (localStorage.getItem('remember')) {
        var remember = JSON.parse(localStorage.getItem('remember'));
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
});