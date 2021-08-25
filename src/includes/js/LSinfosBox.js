var LSinfosBox = new Class({
    initialize: function(options){
      // Default options
      this._options = {
        closeBtn:       0,
        name:           '',
        fxDuration:     500,
        opacity:        0.8,
        autoClose:      3000,
        pre:            false,
      };

      // Load options from argument
      if ($type(options)=='object') {
        Object.each(options, function(val, name) {
          if ($type(this._options[name])) {
            this._options[name]=val;
          }
        },this);
      }

      this.build();

      this.opened = false;
      this.autoClose_timeout = false;
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
      this.ul = false;
    },

    isOpened: function() {
      return this.opened;
    },

    open: function() {
      this.core.setStyle('top',getScrollTop()+10);

      if (this._options.autoClose) {
        this.closeTime = (new Date()).getTime();
        if (this.autoClose_timeout) {
          clearTimeout(this.autoClose_timeout);
        }
        this.autoClose_timeout = this.close.delay(this._options.autoClose, this);
      }

      if (this.opened) {
        console.log('LSinfoBox('+this._options.name+'): already opened');
        return;
      }
      console.log('LSinfoBox('+this._options.name+'): open');
      this.opened = true;
      this.fx.start(0, this._options.opacity);
    },

    close: function(withoutEffect) {
      if (this.opened) {
        console.log('LSinfoBox('+this._options.name+'): close');
        this.opened = false;
        if (withoutEffect==1) {
          this.fx.set(0);
        }
        else {
          this.fx.start(this._options.opacity, 0);
        }
      }
      else {
        console.log('LSinfoBox('+this._options.name+'): already closed');
      }
    },

    addInfo: function(info, clear) {
      if (!info || ($type(info) == 'array' && !info.length)) return;
      if (clear) this.clear();
      if (this.content.innerHTML) {
        // If content is not already in ul, put it in
        if (!this.ul) {
          this.ul = new Element('ul');
          if (this.content.innerHTML) {
            var c_li = new Element('li');
            c_li.set('html', this.content.innerHTML);
            c_li.injectInside(this.ul);
          }
          this.content.empty();
          this.ul.injectInside(this.content);
        }

        // Add li.separator to separate old/new content
        var b_li = new Element('li');
        b_li.addClass('separator');
        b_li.injectInside(this.ul);
      }

      if ($type(info) == "string") {
        if (this.ul) {
          var li = new Element('li');
          if (this._options.pre) {
            var pre = new Element('pre');
            pre.set('html', info);
            pre.injectInside(li);
          }
          else {
            li.set('html', info);
          }
          li.injectInside(this.ul);
        }
        else {
          this.content.set('html', info);
        }
      }
      else if ($type(info) == 'array') {
        if (!this.ul) {
          this.ul = new Element('ul');
          this.ul.injectInside(this.content);
        }
        Array.each(info, function(msg) {
          var li = new Element('li');
          if (this._options.pre) {
            var pre = new Element('pre');
            pre.set('html', msg);
            pre.injectInside(li);
          }
          else {
            li.set('html', msg);
          }
          li.injectInside(this.ul);
        }, this);
      }
      this.open();
    },

    display: function(info) {
      this.addInfo(info, true);
    },

    displayOrAdd: function(info) {
      console.log('LSinfoBox('+this._options.name+').displayOrAdd(): open='+this.opened);
      this.addInfo(info, !this.opened);
    },

    clear: function() {
      console.log('LSinfoBox('+this._options.name+'): clear');
      this.content.empty();
      this.ul = false;
    }
});
