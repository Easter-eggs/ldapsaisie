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

    isOpened: function() {
      return this.opened;
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
      var ul = this.content.getLast("ul");
      var add = 1;
      if (!$type(ul)) {
        add=0;
        ul = new Element('ul');
        if (this.content.innerHTML!="") {
          var c_li = new Element('li');
          c_li.set('html',this.content.innerHTML);
          c_li.injectInside(ul);
          add=1;
        }
        this.content.empty();
        ul.injectInside(this.content);
      }
      if (add) {
        var b_li = new Element('li');
        b_li.set('html','<hr/>');
        b_li.injectInside(ul);
      }
      var li = new Element('li');
      li.set('html',html);
      li.injectInside(ul);
      this.open();
    },

    display: function(html) {
      if ($type(html)) {
        this.content.empty();
        this.content.set('html',html);
      }
      this.open();
    },

    displayInUl: function(html) {
      if ($type(html)) {
        ul = new Element('ul');
        this.content.empty();
        ul.set('html',html);
        ul.inject(this.content);
      }
      this.open();
    },

    displayOrAdd: function(html) {
      if (this.isOpened()) {
        this.addInfo(html);
      }
      else {
        this.displayInUl(html);
      }
    }
});
