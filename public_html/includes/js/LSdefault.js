var LSdefault = new Class({
    initialize: function(){
      // LSdebug
      this.LSdebug = new LSinfosBox({
        name: 'LSdebug',
        fxDuration: 600,
        closeBtn: 1,
        autoClose: 0
      });
      this.LSdebugInfos = $('LSdebug_txt');
      
      // LSerror
      this.LSerror = new LSinfosBox({
        name: 'LSerror',
        opacity: 0.9,
        autoClose: 10000
      });
      this.LSerror_div = $('LSerror_txt');
      
      // LSinfos
      this.LSinfos = new LSinfosBox({name: 'LSinfos'});
      this.LSinfos_div = $('LSinfos_txt');

      // LSjsConfig
      this.LSjsConfigEl = $('LSjsConfig');
      if ($type(this.LSjsConfigEl)) {
        this.LSjsConfig = JSON.decode(atob(this.LSjsConfigEl.innerHTML));
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
        this.LSdebug.display(this.LSdebugInfos.innerHTML);
      }
      
      if (this.LSerror_div.innerHTML != '') {
        this.LSerror.display(this.LSerror_div.innerHTML);
      }
      
      if (this.LSinfos_div.innerHTML != '') {
        this.LSinfos.display(this.LSinfos_div.innerHTML);
      }
      
      // :)
      var getMoo = /moo$/;
      if (getMoo.exec(window.location)) {
        this.moo();
      }
      document.addEvent('keyup',this.onWantMoo.bindWithEvent(this));
      
      this.LStips = new Tips('.LStips', {'text': ""});
      
      if ($type(this.LSjsConfig['keepLSsessionActive'])) {
        this.LSjsConfig['keepLSsessionActive'] = (Math.round(this.LSjsConfig['keepLSsessionActive']*0.70)*1000);
        this.keepLSsession.delay(this.LSjsConfig['keepLSsessionActive'],this);
      }
      
      this.initializeLang();
    },
    
    initializeLang: function() {
      this.LSlang = $('LSlang');
      if ($type(this.LSlang)) {
        this.LSlang_select = $('LSlang_select');
        if (this.LSlang_select) {
          this.LSlang_open=0;
          window.addEvent('click',this.closeLSlang.bind(this));
          this.LSlang.addEvent('click',this.onLSlangClick.bind(this));
          this.LSlang_select.getElements('img').each(function(img) {
            img.addEvent('click',this.onSelectLSlangImgClick.bind(this,img));
          },this);
          document.getElements('.LSlang_hidden').each(function(el) {
            el.dispose();
          },this);
        }
      }
    },
    
    onLSlangClick: function() {
      LSdebug(this.LSlang_select);
      var infos = this.LSlang.getCoordinates();
      this.LSlang_select.setStyle('top',infos.bottom);
      this.LSlang_select.setStyle('left',infos.right);
      this.LSlang_select.setStyle('display','block');
      this.LSlang_open=1;
    },
    
    closeLSlang: function(event) {
      event = new Event(event);
      if (event.target.id!='LSlang') {
        this.LSlang_select.setStyle('display','none');
        this.LSlang_open = 0;
      }
    },
    
    onSelectLSlangImgClick: function(img) {
      window.location='index.php?lang='+img.alt;
    },

    onWantMoo: function(event) {
      event=new Event(event);
      if ((event.shift) && (event.key=='m')) {
        this.moo.run(null,this);
      }
    },

    moo: function() {
      var mooTxt = "<pre>         (__)     .ooooooooooooooooooo.\n         (oo) °°°°0 I love LdapSaisie 0\n   /------\\\/      °ooooooooooooooooooo°\n  / |    ||\n *  /\---/\\\n    ~~   ~~</pre>";
      this.LSinfos.displayOrAdd(mooTxt);
    },

    onLSsession_topDnChange: function() {
      $('LSsession_topDn_form').submit();
    },

    checkAjaxReturn: function(data) {
      this.LSerror.close(0);
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
          this.LSdebug.displayOrAdd(data.LSdebug);
        }
        
        if ($type(data.LSinfos)) {
          this.LSinfos.displayOrAdd(data.LSinfos);
        }
        
        if ($type(data.LSerror)) {
          this.LSerror.displayOrAdd(data.LSerror);
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

    loadingImgDisplay: function(el,position,size) {
      this.loading_img_id++;
      this.loading_img[this.loading_img_id] = new Element('img');
      if (size=='big') {
        var src = this.imagePath('loading');
      }
      else {
        var src = this.imagePath('ajax-loader');
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
          if ($type(el))
            el.destroy();
        },this);
        this.loading_img_id=-1;
      }
      else {
        this.loading_img[id].destroy();
      }
    },
    
    ajaxDisplayDebugAndError: function() {
      var LSdebug_txt = $('LSdebug_txt_ajax');
      if (LSdebug_txt) {
        var debug = LSdebug_txt.innerHTML;
        if (debug) {
          this.LSdebug.displayOrAdd(debug);
        }
      }
      
      var LSerror_txt = $('LSerror_txt_ajax');
      if (LSerror_txt) {
        var error=LSerror_txt.innerHTML;
        if (error) {
          this.LSerror.displayOrAdd(error);
        }
      }
    },
    
    imagePath: function(image) {
      return 'image.php?i=' + image;
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
      new Request({url: 'index_ajax.php', data: {}, onSuccess: this.keepLSsessionComplete.bind(this)}).send();
    },
    
    keepLSsessionComplete: function() {
      LSdebug('Keep LSsession OK');
      this.keepLSsession.delay(this.LSjsConfig['keepLSsessionActive'],this);
    },
    
    log: function(data) {
      this.LSdebug.addInfo(data);
    }

});

window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSdefault = new LSdefault();
});
