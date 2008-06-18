var LSsmoothbox = new Class({
    initialize: function(options) {
      this.over = new Element('div');
      this.over.setProperty('id','over-LSsmoothbox');
      this.over.setStyles({
        width:        '100%',
        height:       '100%',
        opacity:      '0.5',
        position:     'absolute',
        top:          Window.getScrollTop(),
        visibility:   'hidden'
      });
      this.over.injectInside(document.body);
      
      this.win = new Element('div');
      this.win.setProperty('id','win-LSsmoothbox');
      this.win.injectInside(document.body);
      
      this.frame = new Element('div');
      this.frame.setProperty('id','frame-LSsmoothbox');

      this.pnav = new Element('p');
      this.pnav.setProperty('id','pnav-LSsmoothbox');
      
      this.frame.injectInside(this.win);      
      this.pnav.injectInside(this.win);
      
      $$('a.LSsmoothbox').each(function(el) {
        el.addEvent('click',this.clickA.bindWithEvent(this,el));
      },this);
      
      $$('img.LSsmoothbox').each(function(el) {
        el.addEvent('click',this.clickImg.bindWithEvent(this,el));
        el.setStyle('cursor','pointer');
      },this);
      this.fx = {
        over:  this.over.effect('opacity', {duration: 300}).hide(),
        win:   this.win.effect('opacity', {duration: 300}).hide()
      };
    },
    
    clickA: function(event,a) {
      new Event(event).stop();
      this.openURL(a.href);
    },
    
    clickImg: function(event,img) {
      new Event(event).stop();
      this.openImg(img.src);
    },
    
    display: function() {
      this.fx.over.start(0.5);
      this.fx.win.start(1);
    },
    
    openURL: function(href,el) {
      this.refreshElement = el;

      this.over.setStyle('top',Window.getScrollTop());
      
      var winTop = Window.getScrollTop() + ((window.getHeight() - (window.getHeight()*0.8)) /2);
      this.win.setStyles({
        width:        '80%',
        height:       '80%',
        position:     'absolute',
        top:          winTop,
        left:         '10%',
        visibility:   'hidden'
      });
      
      this.frame.setStyles({
        postion:      'absolute',
        width:        '100%',
        height:       '95%',
        border:       'none'
      });
      
      this.pnav.setStyles({
        width:        '100%',
        height:       '5%',
        cursor:       'pointer'
      });
      
      this.pnav.empty();
      this.cancelBtn = new Element('span');
      this.cancelBtn.setHTML('Annuler');
      this.cancelBtn.addEvent('click',this.close.bindWithEvent(this,false));
      this.cancelBtn.injectInside(this.pnav);
      
      this.closeBtn = new Element('span');
      this.closeBtn.setHTML('Valider');
      this.closeBtn.addEvent('click',this.close.bindWithEvent(this,true));
      this.closeBtn.injectInside(this.pnav);
      
      var options = {
          method:     'post',
          update:     this.frame,
          evalScripts: true
      };
      
      new Ajax(href, options).request();
      this.display();
    },
    
    openImg: function(src) {
      var margin = 25
      this.img = new Element('img');
      this.img.setProperty('src',src);
      if (((this.img.height+margin) > window.getHeight())||(this.img.width>window.getWidth())) {
        var rH = window.getHeight() / (this.img.height+margin);
        var rW = window.getWidth() / (this.img.width);
        if (rH > rW) {
          // W
          this.img.height = Math.floor(this.img.height*window.getWidth()/this.img.width);
          this.img.width  = window.getWidth();
        }
        else {
          // H
          this.img.width  = Math.floor(this.img.width*(window.getHeight()-margin)/this.img.height);
          this.img.height = window.getHeight() - margin;
        }
      }
      
      var winTop  = Window.getScrollTop() + ((window.getHeight() - (this.img.height+margin)) /2);
      var winLeft = (window.getWidth() - this.img.width) /2;
      this.win.setStyles({
        width:        this.img.width,
        height:       this.img.height+margin-5,
        position:     'absolute',
        top:          winTop,
        left:         winLeft,
        visibility:   'hidden'
      });
      
      this.frame.setStyles({
        postion:      'absolute',
        width:        '100%',
        height:       this.img.height,
        border:       'none'
      });
      
      this.pnav.setStyles({
        width:        '100%',
        height:       margin-5,
        cursor:       'pointer'
      });
      
      
      this.frame.empty();
      this.img.injectInside(this.frame);
      
      this.pnav.empty();
      this.closeBtn = new Element('span');
      this.closeBtn.setHTML('Fermer');
      this.closeBtn.addEvent('click',this.close.bindWithEvent(this,false));
      this.closeBtn.injectInside(this.pnav);
      
      this.display();
    },
    
    close: function(event,refresh) {
      new Event(event).stop();
      this.fx.win.start(0);
      this.fx.over.start(0);
      if (refresh) {
        try {
          this.refreshElement.refresh();
        }
        catch (e){
          console.log('rater');
        }
      }
      return true;
    }
    
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSsmoothbox = new LSsmoothbox();
});
