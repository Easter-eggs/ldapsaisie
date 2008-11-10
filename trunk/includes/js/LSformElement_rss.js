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
      el.getElements('input.LSformElement_rss').each(function(input) {
        this.addBtnAfter.bind(this)(input);
      }, this);
      el.getElements('a.LSformElement_rss').each(function(a) {
        this.addBtnAfter.bind(this)(a);
      }, this);
    },
    
    addBtnAfter: function(el) {
      var btn = new Element('img');
      btn.setProperties({
        src:    varLSdefault.imagePath('rss.png'),
        alt:    'File RSS'
      });
      btn.addClass('btn');
      btn.injectAfter(el);
      btn.addEvent('click',this.onBtnClick.bind(this,btn));
      varLSdefault.addHelpInfo(btn,'LSformElement_rss','display');
    },
    
    reinitialize: function(el) {
      varLSform.initializeModule('LSformElement_text',el);
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
