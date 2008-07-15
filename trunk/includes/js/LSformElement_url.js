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
      el.getElements('img.LSformElement_url_go_btn').each(function(btn) {
        btn.addEvent('click',this.onGoBtnClick.bind(this,btn));
      }, this);
      el.getElements('img.LSformElement_url_add_favorite_btn').each(function(btn) {
        btn.addEvent('click',this.onAddFavoriteBtnClick.bind(this,btn));
      }, this);
    },
    
    reinitialize: function(el) {
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
      var name = btn.getParent().getFirst().title;
      if (href!="") {
        if (window.sidebar) {
          window.sidebar.addPanel(name,href,'');
        }
        else if(document.all) {
          window.external.AddFavorite(href,name);
        }
        else {
          alert('Fonctionnalité pas encore supportée pour votre navigateur.');
        }
      }
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_url = new LSformElement_url();
});
