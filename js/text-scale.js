ready(() => {
  const elements = document.querySelectorAll('[data-text-scale]');
  elements.forEach(element => {
    const initialFontSize = parseFloat(window.getComputedStyle(element).fontSize);
    element.setAttribute('data-initial-font-size', initialFontSize);
  });
  if (elements.length === 0) return;
  const getFontSize = function(element, maxWidth) {
    const offscreen = element.cloneNode(true)
    offscreen.style.width = 'auto';
    offscreen.style.height = 'auto';
    offscreen.style.display = 'inline-block';
    document.body.appendChild(offscreen);
    let fontSize = element.getAttribute('data-initial-font-size');
    offscreen.style.fontSize = fontSize + 'px';
    if (offscreen.offsetWidth <= maxWidth) {
      offscreen.remove();
      return fontSize + 'px';
    }
    const letterCount = element.innerText.length;
    let i = 0;
    while (i < 100 && offscreen.offsetWidth > maxWidth) {
      const factor = (offscreen.offsetWidth - maxWidth) / letterCount
      fontSize -= factor
      offscreen.style.fontSize = fontSize + 'px';
      i++;
    }
    offscreen.remove();
    return fontSize + 'px';
  }
  const updateScale = () => {
    elements.forEach(element => {
      const parentRect = element.parentNode.getBoundingClientRect();
      const parentStyle = window.getComputedStyle(element.parentNode);
      const maxWidth = parentRect.width - parseFloat(parentStyle.paddingLeft) - parseFloat(parentStyle.paddingRight);
      element.style.fontSize = getFontSize(element, maxWidth)
    });
  }

  const resizeObserver = new ResizeObserver(updateScale);
  elements.forEach(element => {
    resizeObserver.observe(element.parentNode);
  });

  document.fonts.ready.then(() => {
    updateScale();
  });
  updateScale();
});
// ready(() => {
//   const elements = document.querySelectorAll('[data-text-scale]');
//   elements.forEach(element => {
//     const initialFontSize = parseFloat(window.getComputedStyle(element).fontSize);
//     element.setAttribute('data-initial-font-size', initialFontSize);
//   });
//   if (elements.length === 0) return;
//   const getFontSize = function(element,maxWidth) {
//     const offscreen = element.cloneNode(true)
//     offscreen.style.width = 'auto';
//     offscreen.style.height = 'auto';
//     offscreen.style.display = 'inline-block';
//     document.body.appendChild(offscreen);
//     let fontSize = element.getAttribute('data-initial-font-size');
//     offscreen.style.fontSize = fontSize+'px';
//     if (offscreen.offsetWidth <= maxWidth) {
//       offscreen.remove();
//       return fontSize+'px';
//     }
//     const letterCount = element.innerText.length;
//     let i = 0;
//     while (i < 100 && offscreen.offsetWidth > maxWidth)  {
//       const factor =  (offscreen.offsetWidth-maxWidth)/letterCount
//       fontSize-=factor
//       offscreen.style.fontSize = fontSize+'px';
//       i++;
//     }
//     offscreen.remove();
//     return fontSize+'px';
//   }
//   const updateScale = () => {
//     elements.forEach(element => {
//       const parentRect = element.parentNode.getBoundingClientRect();
//       const parentStyle = window.getComputedStyle(element.parentNode);
//       const maxWidth = parentRect.width - parseFloat(parentStyle.paddingLeft) - parseFloat(parentStyle.paddingRight);
//       element.style.fontSize = getFontSize(element,maxWidth)
//     });
//   }
//   window.addEventListener('resize', updateScale );
//   document.fonts.ready.then(() => {
//     updateScale();
//   });
//   updateScale();
// });