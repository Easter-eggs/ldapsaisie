var LSinfosBox = new Class({
    initialize: function(options){
      // Default options
      this._options = {
        closeBtn:       0,
        name:           '',
        fxDuration:     500,
        opacity:        0.8,
        autoClose:      3000
      };
      
      // Load options from argument
      if ($type(options)=='object') {
        $each(options,function(val,name) {
          if ($type(this._options[name])) {
            this._options[name]=val;
          }
        },this);
      }
      
      this.build();
      
      this.opened=0;
    },
    
    build: function() {
      var classes;
      if (this._options.name!='') {
        classes='LSinfosBox '+this._options.name;
      }
      else {
        classes='LSinfosBox'
      }
      
      this.core = new Element('div');
      this.core.addClass(classes);
      this.core.addEvent('dblclick',this.close.bind(this));
      
      if(this._options.closeBtn) {
        this.closeBtn = new Element('span');
        this.closeBtn.addClass(classes);
        this.closeBtn.set('html','X');
        this.closeBtn.addEvent('click',this.close.bind(this));
        this.closeBtn.injectInside(this.core);
      }
      
      this.content = new Element('p');
      this.content.addEvent(classes);
      this.content.injectInside(this.core);
      
      this.fx = new Fx.Tween(
        this.core,
        {
            property: 'opacity',
            duration: this._options.fxDuration,
            fps:      30
        }
      );
      
      this.core.inject(document.body,'top');
    },
    
    open: function() {
      this.core.setStyle('top',getScrollTop()+10);
      
      if (this._options.autoClose>0) {
        this.closeTime = (new Date()).getTime();
        this.autoClose.delay((this._options.autoClose+this._options.fxDuration),this,this.closeTime);
      }
      
      if (this.opened) {
        return true;
      }
      
      this.fx.start(0,this._options.opacity);
      this.opened = 1;
      
    },
    
    close: function(withoutEffect) {
      if (this.opened) {
        this.opened = 0;
        if (withoutEffect==1) {
          this.fx.set(0);
        }
        else {
          this.fx.start(this._options.opacity,0);
        }
      }
    },
    
    autoClose: function(time) {
      if (time==this.closeTime) {
        this.close();
        this.closeTime=0;
      }
    },
    
    addInfo: function(html) {
      if (this.content.innerHTML=='') {
        this.content.set('html',html);
      }
      else {
        var ul = this.content.getLast("ul");
        if (!$type(ul)) {
          ul = new Element('ul');
          var c_li = new Element('li');
          c_li.set('html',this.content.innerHTML);
          c_li.injectInside(ul);
          this.content.empty();
          ul.injectInside(this.content);
        }
        var li = new Element('li');
        li.set('html',html);
        li.injectInside(ul);
      }
      this.open();
    },
    
    display: function(html) {
      if ($type(html)) {
        this.content.empty();
        this.content.set('html',html);
      }
      this.open();
    }
});
