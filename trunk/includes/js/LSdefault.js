var LSdefault = new Class({
    initialize: function(){
      this.LSdebug = $('LSdebug');
      this.LSdebug.addEvent('dblclick',this.LSdebugHidde.bind(this));
      this.LSdebugInfos = $('LSdebug_infos');
      this.LSdebug.setOpacity(0);

      this.LSdebugHidden = $('LSdebug_hidden');
      this.LSdebugHidden.addEvent('click',this.LSdebugHidde.bind(this));
      
      this.LSerror = $('LSerror');
      this.LSerror.setOpacity(0);
      
      this.LSinfos = $('LSinfos');

      this.LSjsConfigEl = $('LSjsConfig');
      if ($type(this.LSjsConfigEl)) {
        this.LSjsConfig = JSON.decode(this.LSjsConfigEl.innerHTML);
      }
      else {
        this.LSjsConfig = [];
      }

      this.loading_img=[];
      this.loading_img_id=-1;
      
      this.LSsession_topDn = $('LSsession_topDn');
      if (this.LSsession_topDn) {
        this.LSsession_topDn.addEvent('change',this.onLSsession_topDnChange.bind(this));
      }
      
      this.fx = {
        LSdebug:  new Fx.Tween(this.LSdebug,{property: 'opacity',duration:600}),
        LSerror:  new Fx.Tween(this.LSerror,{property: 'opacity',duration:500}),
        LSinfos:  new Fx.Tween(this.LSinfos,{property: 'opacity',duration:500})
      };
      
      if (this.LSdebugInfos.innerHTML != '') {
        this.displayDebugBox();
      }
      
      if (this.LSerror.innerHTML != '') {
        this.displayErrorBox();
      }
      
      if (this.LSinfos.innerHTML != '') {
        this.displayInfosBox();
      }
    },

    onLSsession_topDnChange: function() {
      $('LSsession_topDn_form').submit();
    },

    LSdebugHidde: function(){
      this.fx.LSdebug.start(0.8,0);
    },

    checkAjaxReturn: function(data) {
      if ($type(data) == 'object') {
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
        LSdebug('retour non-interprétable');
        this.loadingImgHide();
        return;
      }
    },

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
    
    displayErrorBox: function() {
      this.LSerror.setStyle('top',getScrollTop()+10);
      this.fx.LSerror.start(0,0.8);
      (function(){this.fx.LSerror.start(0.8,0);}).delay(10000, this);
    },
    
    displayInfosBox: function() {
      this.LSinfos.setStyle('top',getScrollTop()+10);
      this.fx.LSinfos.start(0,0.9);
      (function(){this.fx.LSinfos.start(0.9,0);}).delay(5000, this);
    },
    
    displayDebugBox: function() {
      this.LSdebug.setStyle('top',getScrollTop()+10);
      this.fx.LSdebug.start(0,0.8);
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
              varLSform.addTip(el);
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
    }

});

window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSdefault = new LSdefault();
});
