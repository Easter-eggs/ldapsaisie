var LSconfirmBox = new Class({
    initialize: function(options) {
      this._options = options;
      this.create();
      this.display();
    },
    
    create: function() {
      this.box = new Element('div');
      this.box.setProperty('id','box-LSconfirmBox');
      this.box.injectInside(document.body);

      this.title = new Element('p');
      this.title.setProperty('id','title-LSconfirmBox');
      if (this._options.title) {
        this.title.set('html',this._options.title);
      }
      else {
        this.title.set('html','Confirmation');
      };
      this.title.injectInside(this.box)
      
      this.closeBtn = new Element('span');
      this.closeBtn.setProperty('id','closeBtn-LSconfirmBox');
      this.closeBtn.injectInside(this.box);
      this.closeBtn.addEvent('click',this.cancel.bind(this));
      
      this.text = new Element('p');
      this.text.setProperty('id','text-LSconfirmBox');
      if (this._options.text) {
        this.text.set('html',this._options.text);
      }
      else {
        this.text.set('html','You confirm your choice ?');
      }
      this.text.injectInside(this.box);
      
      this.btnsBox = new Element('p');
      this.btnsBox.setProperty('id','btnsBox-LSconfirmBox');
      this.btnsBox.injectInside(this.box);
      
      this.confirmBtn = new Element('span');
      this.confirmBtn.addClass('btn-LSconfirmBox');
      if (this._options.validate_label) {
        this.confirmBtn.set('html',this._options.validate_label);
      }
      else {
        this.confirmBtn.set('html','Validate');
      }
      this.confirmBtn.injectInside(this.btnsBox);
      this.confirmBtn.addEvent('click',this.confirm.bind(this));
      
      this.cancelBtn = new Element('span');
      this.cancelBtn.addClass('btn-LSconfirmBox');
      if (this._options.cancel_label) {
        this.cancelBtn.set('html',this._options.cancel_label);
      }
      else {
        this.cancelBtn.set('html','Cancel');
      }
      this.cancelBtn.injectInside(this.btnsBox);
      this.cancelBtn.addEvent('click',this.cancel.bind(this));
      
      this._purge=0;
      
      this.fx = {
        open:   new Fx.Morph(this.box, {duration: 500, transition: Fx.Transitions.Sine.easeOut, onComplete: this.displayContent.bind(this)}),
        close:  new Fx.Morph(this.box, {duration: 500, transition: Fx.Transitions.Sine.easeOut, onComplete: this.onClose.bind(this)})
      };
      this._scrolling=0;
    },
    
    display: function() {
      this.box.setStyle('display','block');
      this.position(true);
      window.addEvent('resize', this.position.bind(this));
      window.addEvent('scroll', this.positionWhenScrolling.bind(this));
    },
    
    positionWhenScrolling: function(oldValue) {
      if (this._scrolling==0||$type(oldValue)) {
        this._scrolling = 1;
        var current = window.getScrollTop().toInt();
        if (oldValue == current) {
          this.position();
          this._scrolling=0;
        }
        else {
          this.positionWhenScrolling.delay(200,this,current);
        }
      }
    },
    
    displayContent: function() {
      [this.title, this.closeBtn, this.text, this.btnsBox].each(function(el) {
        var fx = new Fx.Tween(el,{duration: 200});
        fx.start('opacity',1);
      },this);
    },
    
    hide: function() {
      this.box.empty();
      this.fx.close.start(this.getStartStyles());
      window.removeEvent('resize', this.position.bind(this));
    },
    
    onClose: function() {
      this.box.setStyle('display','none');
      this.purge();
      if (this._options.onClose) {
        $try(this._options.onClose);
      }
    },
    
    purge: function() {
      this._purge=1;
      this.box.empty();
      this.box.destroy();
      delete this.fx;
    },
    
    getStartStyles: function() {
      if (typeof(this._options.startElement) != 'undefined') {
        var startStyles = {
          top:      this._options.startElement.getCoordinates().top,
          left:     this._options.startElement.getCoordinates().left,
          width:    this._options.startElement.getStyle('width').toInt(),
          opacity:  0
        };
      }
      else {
        var startStyles = {
          top:      '0px',
          left:     '0px',
          width:    '0px',
          opacity:  0
        };
      }
      return startStyles;
    },
    
    getEndStyles: function() {
      if (this._options.width) {
        w = this._options.width;
      }
      else {
        w = 300;
      }
      
      var endStyles = {
        width:    w.toInt()+'px',
        top:      ((window.getHeight()/2)-(this.box.getStyle('height').toInt()/2)-this.box.getStyle('border').toInt()+window.getScrollTop()).toInt(),
        left:     ((window.getWidth()/2)-(w/2)-this.box.getStyle('border').toInt()).toInt(),
        opacity:  1
      };
      return endStyles;
    },
    
    position: function(start) {
      if (this._purge==0) {
        var endStyles = this.getEndStyles();
        if (start) {
          this.box.setStyles(this.getStartStyles());
        }
        this.fx.open.start(endStyles);
      }
    },
    
    confirm: function() {
      this.hide();
      if (this._options.onConfirm) {
        $try(this._options.onConfirm);
      }
    },
    
    cancel: function() {
      this.hide();
      if (this._options.onCancel) {
        $try(this._options.onCancel);
      }
    }
});
