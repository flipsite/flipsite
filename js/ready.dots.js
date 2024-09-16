ready(() => {
  document.querySelectorAll('[data-dots]').forEach((dots) => {
    dots.removeAttribute('data-dots');
    const targetId = dots.getAttribute('data-target');
    const backgrounds = dots.hasAttribute('data-backgrounds') ? JSON.parse(dots.getAttribute('data-backgrounds')) : [];
    dots.removeAttribute('data-backgrounds');
    const scrollDots = new ScrollDots(dots, backgrounds, document.getElementById(targetId));
    window.addEventListener('resize', function() {
      scrollDots.handleResize();
    });
  });
});

function ScrollDots(dots, backgrounds, target) {
  this.observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        console.log('Dot intersecting');
        const element = entry.target;
        const bg = element.getAttribute("data-lazybg");
        element.style.backgroundImage = element.getAttribute("data-lazybg");
        element.removeAttribute("data-lazybg");
        observer.unobserve(element);
      }
    });
  }, {
    root: null,
    rootMargin: "0px",
    threshold: 0
  });
  this.selectedIndex = -1;
  this.visibleItems = -1;
  this.timeout = null;
  this.itemWidth = 1;
  this.dots = dots;
  this.target = target;
  this.itemsCount = target.children.length;
  this.backgrounds = backgrounds;
  this.dotTpl = dots.children[0].cloneNode();
  this.selectedClasses = [];
  this.notSelectedClasses = [];
  var that = this;
  this.dotTpl.getAttribute('data-selected').split(' ').forEach((cls) => {
    if (that.dotTpl.classList.contains(cls)) {
      that.notSelectedClasses.push(cls);
    } else {
      that.selectedClasses.push(cls);
    }
  });
  this.dotTpl.removeAttribute('data-selected')
  for (let i=0; i<this.notSelectedClasses.length; i++) {
    this.dotTpl.classList.remove(this.notSelectedClasses[i])
  }
  for (let i=0; i<this.selectedClasses.length; i++) {
    this.dotTpl.classList.remove(this.selectedClasses[i])
  }
  this.handleResize();
  this.setSelected(0,false);

  this.target.onscroll = function(e) {
    if (that.scrollDisabled) return;
    const index = Math.round(e.target.scrollLeft/(that.itemWidth*that.visibleItems));
    that.setSelected(index,false);
  }
}

ScrollDots.prototype.handleResize = function() {
  const visibleItems = this.countItemsInViewport();
  if (visibleItems === this.visibleItems) {
    return;
  }
  this.visibleItems = visibleItems;
  // Remove all dots
  while (this.dots.firstChild) {
    this.dots.removeChild(this.dots.firstChild);
  }

  // Create new dots
  const that = this;
  const neededDots = Math.ceil(this.itemsCount/this.visibleItems); 
  for (let i=0; i<neededDots; i++) {
    const dot = this.dotTpl.cloneNode()
    if (undefined !== this.backgrounds[i]) {
      dot.setAttribute('data-lazybg','url('+this.backgrounds[i]+')');
    }
    dot.onclick = function(){
      that.setSelected(i,true)
      that.scrollDisabled = true;
      clearTimeout(that.timeout)
      that.timeout = setTimeout(function(){
        that.scrollDisabled = false;
      },1000)
    }
    this.dots.append(dot)
    this.observer.observe(dot);
  }
  this.selectedIndex = -1;
  this.setSelected(0,true);
}

ScrollDots.prototype.setSelected = function(index, scroll){
  if (this.selectedIndex === index) return;
  if (scroll) {
    this.target.scrollTo(index*this.target.offsetWidth, 0);
  }
  for (let i = 0; i < this.dots.children.length; i++) {
    const element = this.dots.children[i];
    const isSelected = (index === i);

    // Remove all classes
    this.selectedClasses.concat(this.notSelectedClasses).forEach(function (className) {
        element.classList.remove(className);
    });

    // Add appropriate classes based on the condition
    const classesToAdd = isSelected ? this.selectedClasses : this.notSelectedClasses;
    classesToAdd.forEach(function (className) {
        element.classList.add(className);
    });
    this.selectedIndex = index;
  } 
}

ScrollDots.prototype.countItemsInViewport = function() {
  this.itemWidth = this.target.children[0].clientWidth;
  return Math.round(this.target.offsetWidth / this.itemWidth); 
}