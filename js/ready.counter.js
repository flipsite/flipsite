ready(() => {
    const observer = new IntersectionObserver(handleIntersection,{
        threshold: 0.1,
    });
    var elements = document.querySelectorAll('[data-counter]').forEach((el)=>{
        observer.observe(el);
    });

    function handleIntersection(entries) {
        entries.map((entry) => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const val = el.querySelector('span');
                observer.unobserve(el);
                let anim = {
                    el:val,
                    from: parseInt(val.innerHTML),
                    to: parseInt(el.getAttribute('data-to')),
                    duration: parseInt(el.getAttribute('data-duration')),
                    start: Date.now()
                };
                update(anim);
            }
        });
    }
    function update(anim) {
        if (anim.start + anim.duration < Date.now()) {
            anim.el.innerHTML = anim.to;
            return;
        }
        var x = easeInOutSine((Date.now() - anim.start)/anim.duration);
        var val = parseInt((anim.to-anim.from)*x + anim.from);
        anim.el.innerHTML = val;
        window.requestAnimationFrame(function(){update(anim)});
    }
    function easeInOutSine(x)  {
        return -(Math.cos(3.14159265359 * x) - 1) / 2;
    }
});