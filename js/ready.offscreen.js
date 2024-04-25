ready(() => {
  const elements = document.querySelectorAll('[class*="offscreen:"]');
  if (!elements) return;
  // const getProgress = (start, end, progress) => {
  //   return start.map((value, index) => {
  //     return value + (end[index] - value) * progress;
  //   });
  // }
  const getTrigger = (element) => {
    const trigger = element.getAttribute('data-trigger');
    switch(trigger) {
      case 'parent':
        return element.parentNode.tagName !== 'BODY' ? element.parentNode : element;
      case 'section':
        while(element.parentNode.tagName !== 'BODY') {
          element = element.parentNode;
        }
        return element;
      default: return element;
    }
  }

  const getStyle = (style) => {
    let clonedStyle = {};
    for (let i = 0; i < style.length; i++) {
      const propertyName = style[i];
      const propertyValue = style.getPropertyValue(propertyName);
      clonedStyle[propertyName] = propertyValue;
    }
    return clonedStyle;
  }
  const getDiff = (offscreen, onscreen) => {
    const diff = {};
    const regex = /^[0-9]+|^webkit/i;
    const floatRegex = /^-?\d*(\.\d+)?$/;
    for (let propertyName in offscreen) {
      if (!regex.test(propertyName)) {
        const propertyValueOffscreen = offscreen[propertyName];
        const propertyValueOnscreen = onscreen[propertyName];
        if (propertyValueOffscreen !== propertyValueOnscreen) {
          if ('transform' === propertyName) {
            const is3d = propertyValueOffscreen.startsWith('matrix3d');
            diff[propertyName] = [
              propertyValueOffscreen.slice(is3d ? 9 : 7, -1).split(', ').map(parseFloat),
              propertyValueOnscreen.slice(is3d ? 9 : 7, -1).split(', ').map(parseFloat),
            ];
          } else if (floatRegex.test(propertyValueOffscreen) && floatRegex.test(propertyValueOnscreen)) {
            diff[propertyName] = [parseFloat(propertyValueOffscreen),parseFloat(propertyValueOnscreen)];
          }
        }
      }
    }
    return diff;
  }

  let appearAnimations = [];
  let scrollAnimations = [];

  elements.forEach((el)=> {
    const isScrollTransform = el.hasAttribute('data-scroll-transform');
    const offscreenClasses = [...el.classList].filter(className => className.startsWith('offscreen'));
    if (!isScrollTransform) {
      appearAnimations.push({
        el: el,         
        tr: getTrigger(el),
        sp: parseFloat(el.getAttribute('data-start') || 0)/100.0,
        ep: parseFloat(el.getAttribute('data-end') || 100)/100.0,
        of: offscreenClasses,
        rp: el.hasAttribute('data-replay')
      });
    } else {
      const offscreenStyle = getStyle(window.getComputedStyle(el));
      el.classList.remove(...offscreenClasses);
      const onscreenStyle = getStyle(window.getComputedStyle(el));
      el.classList.add(...offscreenClasses);
      
      console.log(offscreenStyle.transform)
      scrollAnimations.push({
        el: el,         
        tr: getTrigger(el),
        sp: parseFloat(el.getAttribute('data-start') || 0)/100.0,
        ep: parseFloat(el.getAttribute('data-end') || 100)/100.0,
        di: getDiff(offscreenStyle, onscreenStyle),
        rp: el.hasAttribute('data-replay'),
        lp: 0
      });
    }
    return;
  });
    
  //   const offscreenStyle = window.getComputedStyle(el);
  //   // Loop through the properties of the computed styles
  //   for (let i = 0; i < offscreenStyle.length; i++) {
  //     const propertyName = offscreenStyle[i];
  //     const propertyValue = offscreenStyle.getPropertyValue(propertyName);
  //     console.log(propertyName + ': ' + propertyValue);
  //   }

    
  //   const is3d = offscreenStyle.transform.startsWith('matrix3d');
  //   const offscreen = {
  //     transform: offscreenStyle.transform.slice(is3d ? 9 : 7, -1).split(', ').map(parseFloat),
  //     opacity: parseFloat(offscreenStyle.opacity),
  //   }
  //   el.classList.remove(...offscreenClasses);
  //   const onscreenStyle = window.getComputedStyle(el);
  //   animations.push({
  //     element: el,
  //     trigger: getTrigger(el),
  //     startProgress: parseFloat(el.getAttribute('data-start') || 0),
  //     endProgress: parseFloat(el.getAttribute('data-end') || 1),
  //     offscreenClasses: offscreenClasses,
  //     offscreen: offscreen,
  //     onscreen:{
  //       transform: onscreenStyle.transform.slice(is3d ? 9 : 7, -1).split(', ').map(parseFloat),
  //       opacity: parseFloat(onscreenStyle.opacity),
  //     },
  //   });
  //   el.classList.add(...offscreenClasses);
  // });

  const getOffsetTop = (element) => {
    let offsetTop  = 0;
    do{ offsetTop  += element.offsetTop;
        element = element.offsetParent;
    } while( element );
    return offsetTop;
  }
  const getProgress = (element, startProgress, endProgress) => {
    const rect = element.getBoundingClientRect();
    const pixelsAboveBottom = window.innerHeight+window.scrollY-getOffsetTop(element)
    const percentageOverBottom = Math.min(pixelsAboveBottom/element.clientHeight,1.0);
    return percentageOverBottom < startProgress ? 0 : percentageOverBottom > endProgress ? 1 : (percentageOverBottom-startProgress)/(endProgress-startProgress);
  }
  const setProgress = (element, diff, progress) => {
    for (let propertyName in diff) {
      const propertyValue = diff[propertyName];
      if ('transform' === propertyName) {
        const is3d = propertyValue[0].length > 6;
        element.style.transform = 'matrix'+(is3d ? '3d' : '')+'('+propertyValue[0].map((value, index) => {
          return value + (propertyValue[1][index] - value) * progress;
        }).join(', ')+')';
      } else {
        element.style[propertyName] = propertyValue[0] + (propertyValue[1] - propertyValue[0]) * progress;
      }
    }
  };

  const updateAnimations = () => { 
    appearAnimations.forEach(anim => {
      const progress = getProgress(anim.tr, anim.sp, anim.ep);
      if (progress) {
        anim.el.classList.remove(...anim.of);
        anim.remove = !anim.rp;
      } else if (anim.rp) {
        anim.el.classList.add(...anim.of);
      }
    });
    scrollAnimations.forEach(anim => {
      const progress = getProgress(anim.tr, anim.sp, anim.ep);
      if (!anim.rp && progress < anim.lp) {
        return;
      }
      if (!anim.rp && progress >= 1.0) {
        anim.remove = true;
      }
      setProgress(anim.el, anim.di, progress);
    })
    appearAnimations = appearAnimations.filter(anim => !anim.remove);
    scrollAnimations = scrollAnimations.filter(anim => !anim.remove);
    if (appearAnimations.length + scrollAnimations.length === 0) {
      window.removeEventListener('scroll', updateAnimations );
    }
    return;

    animations.forEach(anim => {
      const rect = anim.trigger.getBoundingClientRect();
      const pixelsAboveBottom = window.innerHeight+window.scrollY-getOffsetTop(anim.trigger)
      const percentageOverBottom = Math.min(pixelsAboveBottom/anim.trigger.clientHeight,1.0);
      const progress = percentageOverBottom < anim.startProgress ? 0 : percentageOverBottom > anim.endProgress ? 1 : (percentageOverBottom-anim.startProgress)/(anim.endProgress-anim.startProgress);

      if (percentageOverBottom > 0.2) {
        anim.element.classList.remove(...anim.offscreenClasses);
      } else {
        anim.element.classList.add(...anim.offscreenClasses);
      }

      // Scroll transform
      //anim.element.style.transform = 'matrix'+(anim.onscreen.transform.length > 6 ? '3d' : '')+'('+getProgress(anim.offscreen.transform,anim.onscreen.transform,progress).join(', ')+')';
      //anim.element.style.opacity = getProgress([anim.offscreen.opacity],[anim.onscreen.opacity],progress)[0];
    });
  };
  window.addEventListener('scroll', updateAnimations );
  updateAnimations();
  
});