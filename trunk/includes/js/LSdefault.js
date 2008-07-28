var LSdefault = new Class({
    initialize: function(){
      this.LSdebug = $('LSdebug');
      this.LSdebugInfos = $('LSdebug_infos');
      this.LSdebug.setOpacity(0);

      this.LSdebugHidden = $('LSdebug_hidden');
      this.LSdebugHidden.addEvent('click',this.onLSdebugHiddenClick.bindWithEvent(this));
      
      this.LSerror = $('LSerror');
      this.LSerror.setOpacity(0);

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
        LSerror:  new Fx.Tween(this.LSerror,{property: 'opacity',duration:500})
      };
      
      if (this.LSdebugInfos.innerHTML != '') {
        this.displayDebugBox();
      }
      
      if (this.LSerror.innerHTML != '') {
        this.displayErrorBox();
      }
    },

    onLSsession_topDnChange: function() {
      $('LSsession_topDn_form').submit();
    },

    onLSdebugHiddenClick: function(event){
      new Event(event).stop();
      this.fx.LSdebug.start(0.8,0);
    },

    checkAjaxReturn: function(data) {
      if (typeof(data) == 'object') {
        if (typeof(data.imgload) != "undefined") {
          this.loadingImgHide(data.imgload);
        }
        else {
          this.loadingImgHide();
        }
        
        if (typeof(data.LSdebug) != "undefined") {
          LSdebug(data.LSdebug);
          this.displayDebug(data.LSdebug);
        }
        
        if (typeof(data.LSerror) != "undefined") {
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

    displayErrorBox: function() {
      this.LSerror.setStyle('top',getScrollTop()+10);
      this.fx.LSerror.start(0,0.8);
      (function(){this.fx.LSerror.start(0.8,0);}).delay(10000, this);
    },
    
    displayDebugBox: function() {
      this.LSdebug.setStyle('top',getScrollTop()+10);
      this.fx.LSdebug.start(0,0.8);
    },

    loadingImgDisplay: function(el,position,size) {
      this.loading_img_id++;
      this.loading_img[this.loading_img_id] = new Element('img');
      if (size=='big') {
        var src = 'templates/images/loading.gif';
      }
      else {
        var src = 'templates/images/ajax-loader.gif';
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
    }

});

window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSdefault = new LSdefault();
});
