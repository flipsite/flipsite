ready(() => {
  const elements = document.querySelectorAll('[data-animate]');
  if (!elements) return;
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
      case 'body':
        return document.body;
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
    const offscreenClasses = el.getAttribute('data-animate').split(' ');
    const event = el.getAttribute('data-event') || 'enter';
    if (!isScrollTransform) {
      if ('enter' === event) {
        offscreenClasses.forEach((className) => {
          el.classList.toggle(className)
        });
      }
      appearAnimations.push({
        el: el,
        ev: event,
        tr: getTrigger(el),
        sp: el.getAttribute('data-start') || '0%',
        ep: el.getAttribute('data-end') || '100%',
        of: offscreenClasses,
        rp: el.hasAttribute('data-replay'),
        st: false
      });
    } else {
      const onscreenStyle = getStyle(window.getComputedStyle(el));
      offscreenClasses.forEach((className) => {
        el.classList.toggle(className)
      });
      const offscreenStyle = getStyle(window.getComputedStyle(el));
      if('enter' === event) {
        offscreenClasses.forEach((className) => {
          el.classList.toggle(className)
        });
      }
      scrollAnimations.push({
        el: el,
        ev: event,
        tr: getTrigger(el),
        sp: el.getAttribute('data-start') || '0%',
        ep: el.getAttribute('data-end') || '100%',
        di: 'enter' === event ? getDiff(offscreenStyle, onscreenStyle) : getDiff(onscreenStyle, offscreenStyle),
        rp: el.hasAttribute('data-replay'),
        lp: 0,
        st: false
      });
    }
    return;
  });
    
  const getOffsetTop = (element) => {
    let offsetTop  = 0;
    do{ offsetTop  += parseInt(element.offsetTop);
        element = element.offsetParent;
    } while( element );
    return offsetTop;
  }
  // Returns progress in percentage value 0...1
  const getProgress = (element, event, startProgress, endProgress) => {
    if (startProgress.indexOf('px') !== -1) {
      startProgress = parseFloat(startProgress)/element.offsetHeight;
    } else startProgress = parseFloat(startProgress)/100;
    if (endProgress.indexOf('px') !== -1) {
      endProgress = parseFloat(endProgress)/element.offsetHeight;
    } else endProgress = parseFloat(endProgress)/100;
    const offsetTop = getOffsetTop(element);
    if ('enter' === event) {
      const pixelsAboveBottom = window.innerHeight+window.scrollY-offsetTop
      const percentageOverBottom = Math.min(pixelsAboveBottom/element.clientHeight,1.0);
      return percentageOverBottom < startProgress ? 0 : percentageOverBottom > endProgress ? 1 : (percentageOverBottom-startProgress)/(endProgress-startProgress);
    } else if ('exit' === event) {
      const pixelsAboveTop = window.scrollY-offsetTop
      const percentagAboveTop = Math.min(pixelsAboveTop/element.clientHeight,1.0);
      return percentagAboveTop < startProgress ? 0 : percentagAboveTop > endProgress ? 1 : (percentagAboveTop-startProgress)/(endProgress-startProgress);
    }
    else return 1
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
      const progress = getProgress(anim.tr, anim.ev, anim.sp, anim.ep);
      if ((progress && !anim.st) || (!progress && anim.st)) {
        anim.of.forEach((cls) => {
          anim.el.classList.toggle(cls)
        })
        anim.st = !anim.st;
        anim.remove = !anim.rp;
      }
    });
    scrollAnimations.forEach(anim => {
      const progress = getProgress(anim.tr, anim.ev, anim.sp, anim.ep);
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
  };
  window.addEventListener('scroll', updateAnimations );
  updateAnimations();
  
});