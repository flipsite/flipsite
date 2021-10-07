function submit(id) {
    var validate = function(data, value) {
        var format = data.split('|');
        if ('string'===format[0]) {
            switch (format[1]) {
                case 'email': return validateEmail(value);
                case 'phone': return validatePhone(value);
                default: return value.length > 0;
            }
        }
        return false;
    }
    function validateEmail(email) {
        const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    function validatePhone(number) {
        const re = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im;
        return re.test(String(number).toLowerCase());
    }
    function shake(el) {
        el.getAttribute('class').split(' ').forEach((cls => {
            if (cls.indexOf('invalid:') === 0) {
                cls = cls.replace('invalid:','');
                el.classList.add(cls);
                setTimeout(function(){
                    el.classList.remove(cls);
                },1000)
            }
        }))
    }
    var form = undefined === id ? document.querySelector('form') : document.getElementById(id);
    var json = JSON.parse(form.getAttribute('data-validate').replaceAll("'",'"'));
    var ok = {};
    for (var name in json.data) {
        ok[name] = validate(json.data[name], form.querySelector('[name='+name+']').value);
    }
    var required = {}
    for (var i in json.required) {
        var params = json.required[i].split('|');
        var isOk = false;
        for (var j in params) {
            if (ok[params[j]]) {
                isOk = true;
            }
        }
        required[json.required[i]] = isOk;
    }
    var ok = true;
    for (var i in required) {
        if (!required[i]) {
            ok = false;
            i.split('|').forEach((name)=>shake(document.querySelector('[name='+name+']')));
        }
    }
    if (ok) form.submit();
}