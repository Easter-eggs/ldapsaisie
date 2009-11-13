var LSformElement_url = new Class({
    initialize: function(){
      this.initialiseLSformElement_url();
      if (typeof(varLSform) != "undefined") {
        varLSform.addModule("LSformElement_url",this);
      }
    },
    
    initialiseLSformElement_url: function(el) {
      if (typeof(el) == 'undefined') {
        el = document;
      }
      el.getElements('input.LSformElement_url').each(function(input) {
        this.addBtnAfter.bind(this)(input);
      }, this);
      el.getElements('a.LSformElement_url').each(function(a) {
        this.addBtnAfter.bind(this)(a);
      }, this);
    },
    
    addBtnAfter: function(el) {
      var btn_go = new Element('img');
      btn_go.setProperties({
        src:    varLSdefault.imagePath('url_go.png')
      });
      btn_go.addClass('btn');
      btn_go.injectAfter(el);
      btn_go.addEvent('click',this.onGoBtnClick.bind(this,btn_go));
      varLSdefault.addHelpInfo(btn_go,'LSformElement_url','go');
      
      if (this.isAddFavoriteSupportedBrowser()) {
        var btn_fav = new Element('img');
        btn_fav.setProperties({
          src:    varLSdefault.imagePath('url_add.png')
        });
        btn_fav.addClass('btn');
        btn_fav.injectAfter(btn_go);
        btn_fav.addEvent('click',this.onAddFavoriteBtnClick.bind(this,btn_fav));
        varLSdefault.addHelpInfo(btn_fav,'LSformElement_url','fav');
      }
    },
    
    reinitialize: function(el) {
      varLSform.initializeModule('LSformElement_text',el);
      this.initialiseLSformElement_url(el);
    },
    
    onGoBtnClick: function(btn) {
      var href = btn.getParent().getFirst().href;
      if (typeof(href)=="undefined") {
        href = btn.getParent().getFirst().value;
      }
      if (href!="") {
        window.open(href,'_blank');
      }
    },
    
    onAddFavoriteBtnClick: function(btn) {
      var href = btn.getParent().getFirst().value;
      if (typeof(href)=="undefined") {
        href = btn.getParent().getFirst().href;
      }
      if (href!="") {
        var name = href;
        if (window.sidebar) {
          window.sidebar.addPanel(name,href,'');
        }
        else if(window.external) {
          window.external.AddFavorite(href,name);
        }
      }
    },
    
    isAddFavoriteSupportedBrowser: function() {
      if (window.sidebar || window.external) {
        return 1;
      }
      return;
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_url = new LSformElement_url();
});
