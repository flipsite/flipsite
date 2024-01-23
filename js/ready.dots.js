ready(() => {
  document.querySelectorAll('[data-dots]').forEach((dots) => {
    const targetId = dots.getAttribute('data-target');
    const scrollDots = new ScrollDots(dots, document.getElementById(targetId));
    window.addEventListener('resize', function(){
      scrollDots.handleResize();
    });
  });
});

function ScrollDots(dots, target) {
  this.selectedIndex = -1;
  this.visibleItems = -1;
  this.timeout = null;
  this.itemWidth = 1;
  this.dots = dots;
  this.target = target;
  this.itemsCount = target.children.length;
  this.dotTpl = dots.children[0].cloneNode();
  this.selectedClasses = [];
  this.notSelectedClasses = [];
  for (let i=0; i<this.dotTpl.classList.length; i++) {
    const cls = this.dotTpl.classList[i];
    if (cls.startsWith('!selected:')) {
      this.notSelectedClasses.push(cls.replace('!selected:',''));
    } else if (cls.startsWith('selected:')) {
      this.selectedClasses.push(cls.replace('selected:',''));
    }
  }
  for (let i=0; i<this.notSelectedClasses.length; i++) {
    this.dotTpl.classList.remove('!selected:'+this.notSelectedClasses[i])
  }
  for (let i=0; i<this.selectedClasses.length; i++) {
    this.dotTpl.classList.remove('selected:'+this.selectedClasses[i])
  }
  this.handleResize();
  this.setSelected(0,false);

  const that = this;
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
    dot.onclick = function(){
      that.setSelected(i,true)
      that.scrollDisabled = true;
      clearTimeout(that.timeout)
      that.timeout = setTimeout(function(){
        that.scrollDisabled = false;
      },1000)
    }
    this.dots.append(dot)
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