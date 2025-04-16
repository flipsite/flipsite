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
                    timing: el.getAttribute('data-timing') ?? 'ease-in-out',
                    start: Date.now(),
                    thousands: el.getAttribute('data-thousands') ?? 'none',
                };
                update(anim);
            }
        });
    }
    function update(anim) {
        if (anim.start + anim.duration < Date.now()) {
            anim.el.innerHTML = formatNumber(anim.to, anim.thousands);
            return;
        }
        let x; 
        switch (anim.timing) {
            case 'ease-linear': x = linear((Date.now() - anim.start)/anim.duration); break;
            case 'ease-in': x = easeInSine((Date.now() - anim.start)/anim.duration); break;
            case 'ease-out': x = easeOutSine((Date.now() - anim.start)/anim.duration); break;
            case 'ease-in-out': x = easeInOutSine((Date.now() - anim.start)/anim.duration); break;
        }
        let val = parseInt((anim.to-anim.from)*x + anim.from);
        val = formatNumber(val, anim.thousands);
        anim.el.innerHTML = val;
        window.requestAnimationFrame(function(){update(anim)});
    }
    function formatNumber(num, thousands) {
        switch (thousands) {
            case 'space': return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "&nbsp;");
            case 'comma': return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            case 'period': return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
        return num;
    }
    function linear(x) {
        return x;
    }
    function easeInSine(x) {
        return 1 - Math.cos((x * Math.PI) / 2);
    }
    function easeOutSine(x) {
        return Math.sin((x * Math.PI) / 2);
    }
    function easeInOutSine(x)  {
        return -(Math.cos(3.14159265359 * x) - 1) / 2; 
    }
});