var LSdefault = new Class({
    initialize: function(){
      // LSdebug
      this.LSdebug = $('LSdebug');
      this.LSdebug.addEvent('dblclick',this.hideLSdebug.bind(this));
      this.LSdebugInfos = $('LSdebug_infos');
      this.LSdebug.setOpacity(0);
      this.LSdebug_open = 0;

      this.LSdebugHidden = $('LSdebug_hidden');
      this.LSdebugHidden.addEvent('click',this.hideLSdebug.bind(this));
      
      // LSerror
      this.LSerror = $('LSerror');
      this.LSerror.setOpacity(0);
      this.LSerror_open = 0;
      this.LSerror.addEvent('dblclick',this.hideLSerror.bind(this));
      
      // LSinfos
      this.LSinfos = $('LSinfos');

      // FX
      this.fx = {
        LSdebug:  new Fx.Tween(this.LSdebug,{property: 'opacity',duration:600}),
        LSerror:  new Fx.Tween(this.LSerror,{property: 'opacity',duration:500}),
        LSinfos:  new Fx.Tween(this.LSinfos,{property: 'opacity',duration:500})
      };
      
      // LSjsConfig
      this.LSjsConfigEl = $('LSjsConfig');
      if ($type(this.LSjsConfigEl)) {
        this.LSjsConfig = JSON.decode(this.LSjsConfigEl.innerHTML);
      }
      else {
        this.LSjsConfig = [];
      }

      this.loading_img=[];
      this.loading_img_id=-1;

      // LSsession_topDn      
      this.LSsession_topDn = $('LSsession_topDn');
      if (this.LSsession_topDn) {
        this.LSsession_topDn.addEvent('change',this.onLSsession_topDnChange.bind(this));
      }
      
      // Display Infos
      if (this.LSdebugInfos.innerHTML != '') {
        this.displayDebugBox();
      }
      
      if (this.LSerror.innerHTML != '') {
        this.displayErrorBox();
      }
      
      if (this.LSinfos.innerHTML != '') {
        this.displayInfosBox();
      }
      
      // :)
      var getMoo = /moo$/;
      if (getMoo.exec(window.location)) {
        this.moo();
      }
      document.addEvent('keyup',this.onWantMoo.bindWithEvent(this));
      
      this.LStips = new Tips('.LStips');
      
      if ($type(this.LSjsConfig['keepLSsessionActive'])) {
        this.LSjsConfig['keepLSsessionActive'] = (Math.round(this.LSjsConfig['keepLSsessionActive']*0.70)*1000);
        this.keepLSsession.delay(this.LSjsConfig['keepLSsessionActive'],this);
      }
    },

    onWantMoo: function(event) {
      event=new Event(event);
      if ((event.control) && (event.shift) && (event.key=='m')) {
        this.moo.run(null,this);
      }
    },

    moo: function() {
      var mooTxt = "         (__)     .ooooooooooooooooooo.\n         (oo) °°°°0 I love LdapSaisie 0\n   /------\\\/      °ooooooooooooooooooo°\n  / |    ||\n *  /\---/\\\n    ~~   ~~";
      var ulMoo = this.LSinfos.getElement('ul'); 
      var preMoo = new Element('pre');
      preMoo.set('html',mooTxt);
      if ($type(ulMoo)) {
        ulMoo.empty();
        var liMoo = new Element('li');
        liMoo.injectInside(ulMoo);
        preMoo.injectInside(liMoo);
      }
      else {
        this.LSinfos.empty();
        preMoo.injectInside(this.LSinfos);
      }
      this.displayInfosBox();
    },

    onLSsession_topDnChange: function() {
      $('LSsession_topDn_form').submit();
    },

    checkAjaxReturn: function(data) {
      this.hideLSerror();
      if ($type(data) == 'object') {
        if (($type(data.LSredirect)) && (!$type(data.LSdebug)) ) {
          document.location = data.LSredirect;
          return true;
        }
        
        if ($type(data.imgload)) {
          this.loadingImgHide(data.imgload);
        }
        else {
          this.loadingImgHide();
        }
        
        if ($type(data.LSdebug)) {
          LSdebug(data.LSdebug);
          this.displayDebug(data.LSdebug);
        }
        
        if ($type(data.LSinfos)) {
          this.displayInfos(data.LSinfos);
        }
        
        if ($type(data.LSerror)) {
          this.displayError(data.LSerror);
          return;
        }
        return true;
      }
      else {
        LSdebug('Non computable return value');
        this.loadingImgHide();
        return;
      }
    },

    /*
     * Set and Display Methods
     */
    displayError: function(html) {
      this.LSerror.set('html',html);
      this.displayErrorBox();
    },

    displayDebug: function(html) {
      this.LSdebugInfos.set('html',html);
      this.displayDebugBox();
    },
    
    displayInfos: function(html) {
      this.LSinfos.set('html',html);
      this.displayInfosBox();
    },
    
    /*
     * Display Methods
     */
    displayErrorBox: function() {
      this.LSerror.setStyle('top',getScrollTop()+10);
      if (this.LSerror_open) {
        return true;
      }
      this.fx.LSerror.start(0,0.8);
      this.LSerror_open = 1;
    },
    
    displayDebugBox: function() {
      this.LSdebug.setStyle('top',getScrollTop()+10);
      if (this.LSdebug_open) {
        return true;
      }
      this.fx.LSdebug.start(0,0.8);
      this.LSdebug_open = 1;
    },
    
    /*
     * Hide Methods
     */
    hideLSdebug: function(){
      if (this.LSdebug_open) {
        this.fx.LSdebug.start(0.8,0);
        this.LSdebug_open = 0;
      }
    },
    
    hideLSerror: function(){
      if (this.LSerror_open) {
        this.fx.LSerror.start(0.9,0);
        this.LSerror_open = 0;
      }
    },

    displayInfosBox: function() {
      this.LSinfos.setStyle('top',getScrollTop()+10);
      this.fx.LSinfos.start(0,0.9);
      (function(){this.fx.LSinfos.start(0.9,0);}).delay(5000, this);
    },

    loadingImgDisplay: function(el,position,size) {
      this.loading_img_id++;
      this.loading_img[this.loading_img_id] = new Element('img');
      if (size=='big') {
        var src = this.imagePath('loading.gif');
      }
      else {
        var src = this.imagePath('ajax-loader.gif');
      }
      this.loading_img[this.loading_img_id].src=src;
      if (position=='inside') {
        this.loading_img[this.loading_img_id].injectInside(el);
      }
      else {
        this.loading_img[this.loading_img_id].injectAfter(el);
      }
      return this.loading_img_id;
    },

    loadingImgHide: function(id) {
      if (isNaN(id)) {
        this.loading_img.each(function(el)  {
          if (typeof(el) != 'undefined')
            el.destroy();
        },this);
        this.loading_img_id=-1;
      }
      else {
        this.loading_img[id].destroy();
      }
    },
    
    ajaxDisplayDebugAndError: function() {
      var LSdebug_txt = $('LSdebug_txt');
      if (LSdebug_txt) {
        var debug = LSdebug_txt.innerHTML;
        if (debug) {
          this.displayDebug(debug.toString());
        }
      }
      
      var LSerror_txt = $('LSerror_txt');
      if (LSerror_txt) {
        var error=LSerror_txt.innerHTML;
        if (error) {
          this.displayError(error.toString());
        }
      }
    },
    
    imagePath: function(image) {
      return this.LSjsConfig['LS_IMAGES_DIR'] + '/' + image;
    },
    
    getParams: function(name) {
      if ($type(this.LSjsConfig[name])) {
        return this.LSjsConfig[name];
      }
      return new Hash();
    },
    
    addHelpInfo: function(el,group,name) {
      if ($type(this.LSjsConfig['helpInfos'])) {
        if ($type(el)=='element') {
          if ($type(this.LSjsConfig['helpInfos'][group])) {
            if ($type(this.LSjsConfig['helpInfos'][group][name])) {
              this.addTip(el);
              el.store('tip:title',this.LSjsConfig['helpInfos'][group][name]);
              el.store('tip:text',"");
            }
          }
        }
      }
    },
    
    setHelpInfo: function(el,group,name) {
      if ($type(this.LSjsConfig['helpInfos'])) {
        if ($type(el)=='element') {
          if ($type(this.LSjsConfig['helpInfos'][group])) {
            if ($type(this.LSjsConfig['helpInfos'][group][name])) {
              el.store('tip:title',this.LSjsConfig['helpInfos'][group][name]);
              el.store('tip:text',"");
            }
          }
        }
      }
    },
    
    addTip: function(el) {
      this.LStips.attach(el);
    },
    
    removeTip: function(el) {
      this.LStips.detach(el);
    },
    
    keepLSsession: function() {
      LSdebug('Keep LSsession');
      data: {}
      new Request({url: 'index_ajax.php', data: {}, onSuccess: this.keepLSsessionComplete.bind(this)}).send();
    },
    
    keepLSsessionComplete: function() {
      LSdebug('Keep LSsession OK');
      this.keepLSsession.delay(this.LSjsConfig['keepLSsessionActive'],this);
    }

});

window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSdefault = new LSdefault();
});
