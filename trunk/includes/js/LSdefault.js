var LSdefault = new Class({
    initialize: function(){
      this.LSdebug = $('LSdebug');
      this.LSdebugInfos = $('LSdebug_infos');
      this.LSdebug.setOpacity(0);
      if (this.LSdebugInfos.innerHTML != '') {
        this.displayDebugBox();
      }

      this.LSdebugHidden = $('LSdebug_hidden');
      this.LSdebugHidden.addEvent('click',this.onLSdebugHiddenClick.bindWithEvent(this));
      this.LSerror = $('LSerror');
      this.LSerror.setOpacity(0);
      if (this.LSerror.innerHTML != '') {
        this.displayLSerror();
      }
      
      this.loading_img=[];
      LSdebug(this.loading_img);
      this.loading_img_id=-1;
      
      this.LSsession_topDn = $('LSsession_topDn');
      if (this.LSsession_topDn) {
        this.LSsession_topDn.addEvent('change',this.onLSsession_topDnChange.bind(this));
      }
    },

    onLSsession_topDnChange: function() {
      $('LSsession_topDn_form').submit();
    },

    onLSdebugHiddenClick: function(event){
      new Event(event).stop();
      new Fx.Style(this.LSdebug,'opacity',{duration:500}).start(1,0);
    },

    displayDebugBox: function() {
      this.LSdebug.setStyle('top',getScrollTop()+10);
      new Fx.Style(this.LSdebug,'opacity',{duration:500}).start(0,0.8);
    },

    displayError: function(html) {
      this.LSerror.empty();
      this.LSerror.setHTML(html);
      this.displayLSerror();
    },

    displayDebug: function(html) {
      this.LSdebug.empty();
      this.LSdebug.setHTML(html);
      this.displayDebugBox();
    },

    displayLSerror: function() {
      this.LSerror.setStyle('top',getScrollTop()+10);
      new Fx.Style(this.LSerror,'opacity',{duration:500}).start(0,0.8);
      (function(){new Fx.Style(this.LSerror,'opacity',{duration:500}).start(0.8,0);}).delay(5000, this);
    },

    loadingImgDisplay: function(el,position) {
      this.loading_img_id++;
      this.loading_img[this.loading_img_id] = new Element('img');
      this.loading_img[this.loading_img_id].src='templates/images/ajax-loader.gif';
      if (position=='inside') {
        this.loading_img[this.loading_img_id].injectInside(el);
      }
      else {
        this.loading_img[this.loading_img_id].injectAfter(el);
      }
      LSdebug(this.loading_img_id);
      return this.loading_img_id;
    },

    loadingImgHide: function(id) {
      if (isNaN(id)) {
        this.loading_img.each(function(el)  {
          if (typeof(el) != 'undefined')
            el.remove();
        },this);
        this.loading_img_id=-1;
      }
      else {
        this.loading_img[id].remove();
      }
    }

});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSdefault = new LSdefault();
});

LSdebug_active = 0;

function LSdebug() {
    if (LSdebug_active != 1) return;
    if (typeof console == 'undefined') return;
    console.log.apply(this, arguments);
}
