var LSsmoothbox = new Class({
    initialize: function(options) {
      
      this.build();
      
      // Events
      $$('a.LSsmoothbox').each(function(el) {
        el.addEvent('click',this.clickA.bindWithEvent(this,el));
      },this);
      
      $$('img.LSsmoothbox').each(function(el) {
        el.addEvent('click',this.clickImg.bindWithEvent(this,el));
        el.setStyle('cursor','pointer');
      },this);
      
      // Fx
      this.fx = {
        over:      new Fx.Tween(this.over, {property: 'opacity', duration: 300}),
        winOpen:   new Fx.Morph(this.win, {duration: 500, transition: Fx.Transitions.Sine.easeOut, onStart: this.hideContent.bind(this), onComplete: this.displayContent.bind(this)}),
        winClose:  new Fx.Morph(this.win, {duration: 500, transition: Fx.Transitions.Sine.easeOut, onStart: this.hideContent.bind(this), onComplete: this.resetWin.bind(this)})
      };
      
      this.asNew(options);
      
      window.addEvent('resize', this.position.bind(this));
      window.addEvent('scroll', this.positionWhenScrolling.bind(this));
    },
    
    build: function() {
      this.over = new Element('div');
      this.over.setProperty('id','over-LSsmoothbox');
      this.over.setStyles({
        position: 'absolute',
        left: '0px',
        top: '0px',
        width: '100%',
        height: '100%',
        zIndex: 2,
        backgroundColor: '#000',
        opacity:          0
      });
      this.over.injectInside(document.body);
      
      this.win = new Element('div');
      this.win.setProperty('id','win-LSsmoothbox');
      this.win.injectInside(document.body);
      
      this.frame = new Element('div');
      this.frame.setProperty('id','frame-LSsmoothbox');
      
      this.frame.injectInside(this.win);
      
      this.closeBtn = new Element('span');
      this.closeBtn.setProperty('id','closeBtn-LSsmoothbox');
      this.closeBtn.injectInside(this.win);
      this.closeBtn.addEvent('click',this.closeConfirm.bind(this));
      
      this._displayValidBtn = false;
      this.validBtn = new Element('span');
      this.validBtn.setProperty('id','validBtn-LSsmoothbox');
      this.validBtn.set('html','Valider');
      this.validBtn.injectInside(this.win);
      this.validBtn.addEvent('click',this.valid.bindWithEvent(this,true));
    },
    
    asNew: function(options) {
      this._options = ($type(options))?option:{};
      
      // Listeners
      this.listeners = {
        close:    new Array(),
        valid:    new Array(),
        cancel:   new Array()
      };
            
      this._closeConfirm = true;
      this._closeConfirmOpened = 0;
      
      this._open=0;
      this._scrolling=0;
    },
    
    position: function(){ 
      if (this._open==1) {
        this.overPosition();
        
        var endStyles = this.getEndStyles();
        this.fx.winOpen.start({
          width:    endStyles.width,
          height:   endStyles.height,
          top:      endStyles.top,
          left:     endStyles.left
        });
      }
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
    
    overPosition: function() {
      var h = window.getScrollHeight()+'px'; 
      var w = window.getScrollWidth()+'px'; 
      this.over.setStyles({
        top:    '0px',
        height: h,
        width:  w
      });
    },
    
    getStartStyles: function() {
      if (typeof(this.openOptions.startElement) != 'undefined') {
        var startStyles = {
          top:      this.openOptions.startElement.getCoordinates().top,
          left:     this.openOptions.startElement.getCoordinates().left,
          width:    this.openOptions.startElement.getStyle('width'),
          height:   this.openOptions.startElement.getStyle('height')
        };
      }
      else {
        var startStyles = {
          top:    '0px',
          left:   '0px',
          width:  '0px',
          height: '0px'
        };
      }
      return startStyles;
    },
    
    getEndStyles: function() {
      if (this.openOptions.width) {
        w = this.openOptions.width;
      }
      else {
        w = window.getWidth() * 0.9;
      }
      if (this.openOptions.height) {
        h = this.openOptions.height;
      }
      else {
        h = window.getHeight() * 0.8;
      }
      
      var endStyles = {
        width:    w.toInt(),
        height:   h.toInt(),
        top:      ((window.getHeight()/2)-(h/2)-this.win.getStyle('border').toInt()+window.getScrollTop()).toInt(),
        left:     ((window.getWidth()/2)-(w/2)-this.win.getStyle('border').toInt()).toInt()
      };
      return endStyles;
    },
    
    
    open: function() {
      this._open=1;
      this.overPosition();
      this.fx.over.start(0.7);
      var startStyles = this.getStartStyles();
      var endStyles = this.getEndStyles();  
      
      
      this.closeStyles = startStyles;
      this.win.setStyles(startStyles);

      this.fx.winOpen.setOptions({onComplete: this.displayContent.bind(this)});
      this.win.setStyle('display','block');
      this.fx.winOpen.start({
        width:    endStyles.width,
        height:   endStyles.height,
        top:      endStyles.top,
        left:     endStyles.left,
        opacity:  [0, 1]
      });
      [this.validBtn,this.closeBtn,this.frame].each(function(el){
        el.setStyle('display','block');
      },this);
    },
    
    hideContent: function() {
      this.validBtn.setStyle('visibility','hidden');
      this.frame.setStyle('visibility','hidden');
      this.closeBtn.setStyle('visibility','hidden');
    },
    
    displayContent: function() {
      if (this._displayValidBtn) {
        this.validBtn.setStyle('visibility','visible'); 
      }
      this.frame.setStyle('visibility','visible');
      this.closeBtn.setStyle('visibility','visible');
    },
    
    closeConfirm: function() {
      if (this._closeConfirm && this._displayValidBtn) {
        if (!this._closeConfirmOpened) {
          this._closeConfirmOpened = 1;
          this.confirmBox = new LSconfirmBox({
            text:         'Etês-vous sur de vouloir fermer cette fênetre et perdre toute les modifications apportées ?',
            startElement: this.closeBtn,
            onConfirm:    this.cancel.bind(this),
            onClose:      (function(){this._closeConfirmOpened=0;}).bind(this)
          });
        }
      }
      else {
        this.cancel();
      }
    },
    
    valid: function() {
      this.close();
      this.fireEvent('valid');
    },
    
    cancel: function() {
      this.close();
      this.fireEvent('cancel');
    },
    
    close: function() {
      if (this._closeConfirm) {
        delete this.confirmBox;
      }
      
      this.fx.over.cancel();
      this.fx.over.start(0);
      this.hideContent();
      this.fx.winClose.start({
        width:    this.closeStyles.width,
        height:   this.closeStyles.height,
        top:      this.closeStyles.top,
        left:     this.closeStyles.left,
        opacity:  [1, 0]
      });
      this._open=0;
      
      [this.validBtn,this.closeBtn,this.frame].each(function(el){
        el.setStyle('display','none');
      },this);
      
      this.fireEvent('close');
      
    },
    
    resetWin: function() {
      this.hideContent();
      this.win.setStyles(this.getStartStyles());
      
    },
    
    clickA: function(event,a) {
      new Event(event).stop();
      this.openURL(a.href,{startElement: a});
    },
    
    clickImg: function(event,img) {
      new Event(event).stop();
      this.openImg(img.src,{startElement: img});
    },
    
    resize: function() {
      var endStyles = this.getEndStyles();
      this.fx.winOpen.cancel();
      this.fx.winOpen.start({
        width:    endStyles.width,
        height:   endStyles.height,
        top:      endStyles.top,
        left:     endStyles.left,
        opacity:  [0, 1]
      });
    },
    
    load: function() {
      this.frame.empty();
      this.loadingImage = new Element('img');
      this.loadingImage.setProperty('src','templates/images/loading.gif');
      this.loadingImage.setProperty('id','loadingImage-LSsmoothbox');
      this.openOptions.width = 120;
      this.openOptions.height = 120;
      this.resize();
      this.loadingImage.injectInside(this.frame);
    },
    
    endLoad: function() {
      this.frame.empty();
    },
    
    openImg: function(src,openOptions) {
      this.hideValidBtn();
      this.openOptions=openOptions;
      this.open();
      this.load();
      this.img = new Asset.image(src, {onload: this.resizeToImage.bind(this)});
    },
    
    resizeToImage: function() {
      if ((this.img.height > this.win.height)||(this.img.width>this.win.width)) {
        var rH = this.win.height / this.img.height;
        var rW = this.win.width / this.img.width;
        if (rH > rW) {
          // W
          this.img.height = Math.floor(this.img.height*this.win.width/this.img.width);
          this.img.width  = this.win.width;
        }
        else {
          // H
          this.img.width  = Math.floor(this.img.width * this.win.height/this.img.height);
          this.img.height = this.win.height;
        }
      }
      this.endLoad();
      this.openOptions.width = this.img.width;
      this.openOptions.height = this.img.height;
      this.resize();
      this.img.injectInside(this.frame);
    },

    displayValidBtn: function() {
      this._displayValidBtn = true;
    },

    hideValidBtn: function() {
      this._displayValidBtn = false;
    },

    openURL: function(href,openOptions) {
      var options = {
          method:       'post',
          update:       this.frame,
          url:          href,
          evalScripts:  true,
          onComplete:   varLSdefault.ajaxDisplayDebugAndError()
      };
      this.displayValidBtn();
      new Request.HTML(options).send();
      this.openOptions = openOptions;
      this.open();
    },
    
    openHTML: function(html,openOptions) {
      this.displayValidBtn();
      this.frame.set('html',html);
      this.openOptions = openOptions;
      this.open();
    },
    
    setOption: function(name,value) {
      this._options[name]=value;
    },
    
    addEvent: function(event,fnct) {
      if ($type(this.listeners[event])) {
        if ($type(fnct)=="function") {
          this.listeners[event].include(fnct);
        }
      }
    },
    
    fireEvent: function(event) {
      LSdebug('LSsmoothbox :: fireEvent('+event+')');
      if ($type(this.listeners[event])) {
        this.listeners[event].each(function(fnct) {
          try {
            fnct(this);
          }
          catch(e) {
            LSdebug('LSsmoothbox :: '+event+'() -> rater');
          }
        },this);
      }
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSsmoothbox = new LSsmoothbox();
});
