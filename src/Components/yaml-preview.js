function copyYaml(el) {
    event.preventDefault();
    var pre = el.parentNode.parentNode.querySelector('pre');
    var yaml = pre.innerHTML.replace(/(<([^>]+)>)/gi, "");

    const textarea = document.createElement('textarea');
    document.body.appendChild(textarea);
    textarea.innerHTML = yaml;
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
}