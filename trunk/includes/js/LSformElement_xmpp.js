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
      el.getElements('img.LSformElement_xmpp_btn').each(function(btn) {
        btn.addEvent('click',this.onBtnClick.bind(this,btn));
      }, this);
    },
    
    reinitialize: function(el) {
      this.initialiseLSformElement_xmpp(el);
    },
    
    onBtnClick: function(btn) {
      var href = btn.getParent().getFirst().href;
      if (typeof(href)=="undefined") {
        href = 'xmpp:'+btn.getParent().getFirst().value;
      }
      if (href!="") {
        location.href = href;
      }
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_xmpp = new LSformElement_xmpp();
});
