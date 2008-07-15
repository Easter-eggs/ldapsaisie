var LSformElement_rss = new Class({
    initialize: function(){
      this.initialiseLSformElement_rss();
      if (typeof(varLSform) != "undefined") {
        varLSform.addModule("LSformElement_rss",this);
      }
    },
    
    initialiseLSformElement_rss: function(el) {
      if (typeof(el) == 'undefined') {
        el = document;
      }
      el.getElements('img.LSformElement_rss_btn').each(function(btn) {
        btn.addEvent('click',this.onBtnClick.bind(this,btn));
      }, this);
    },
    
    reinitialize: function(el) {
      this.initialiseLSformElement_rss(el);
    },
    
    onBtnClick: function(btn) {
      var href = btn.getParent().getFirst().href;
      if (typeof(href)=="undefined") {
        href = btn.getParent().getFirst().value;
      }
      if (href!="") {
        window.open(href,'_blank');
      }
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_rss = new LSformElement_rss();
});
