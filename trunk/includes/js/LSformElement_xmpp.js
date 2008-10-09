var LSformElement_xmpp = new Class({
    initialize: function(){
      this.initialiseLSformElement_xmpp();
      if (typeof(varLSform) != "undefined") {
        varLSform.addModule("LSformElement_xmpp",this);
      }
    },
    
    initialiseLSformElement_xmpp: function(el) {
      if (typeof(el) == 'undefined') {
        el = document;
      }
      el.getElements('input.LSformElement_xmpp').each(function(input) {
        this.addBtnAfter.bind(this)(input);
      }, this);
      el.getElements('a.LSformElement_xmpp').each(function(a) {
        this.addBtnAfter.bind(this)(a);
      }, this);
    },
    
    addBtnAfter: function(el) {
      var btn = new Element('img');
      btn.setProperties({
        src:    varLSdefault.imagePath('xmpp.png'),
        alt:    'Chat',
        title:  'Chat'
      });
      btn.addClass('btn');
      btn.injectAfter(el);
      btn.addEvent('click',this.onBtnClick.bind(this,btn));
    },
    
    reinitialize: function(el) {
      this.initialiseLSformElement_xmpp(el);
    },
    
    onBtnClick: function(btn) {
      var href = btn.getParent().getFirst().href;
      if (typeof(href)=="undefined") {
        href = 'xmpp:'+btn.getParent().getFirst().value;
      }
      if ((href!="")&&(href!="xmpp:")) {
        location.href = href;
      }
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_xmpp = new LSformElement_xmpp();
});
