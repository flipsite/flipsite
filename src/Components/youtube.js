function youtube(el) {
  var icon = el.querySelector('svg');
  if (icon) icon.remove();
  var picture = el.querySelector('picture');
  if (picture) picture.remove();
  el.classList.remove('hover:cursor-pointer');
  el.removeAttribute('onclick')
  var iframe = el.querySelector('iframe');
  iframe.classList.remove('hidden')
  iframe.setAttribute('src', iframe.getAttribute('data-src'));
  iframe.removeAttribute('data-src');
}