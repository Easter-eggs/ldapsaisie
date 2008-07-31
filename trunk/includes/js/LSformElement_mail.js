var LSformElement_mail = new Class({
    initialize: function(){
      this.initialiseLSformElement_mail();
      if (typeof(varLSform) != "undefined") {
        varLSform.addModule("LSformElement_mail",this);
      }
    },
    
    initialiseLSformElement_mail: function(el) {
      if (typeof(el) == 'undefined') {
        el = document;
      }
      el.getElements('img.LSformElement_mail_btn').each(function(btn) {
        btn.addEvent('click',this.onBtnClick.bind(this,btn));
      }, this);
    },
    
    reinitialize: function(el) {
      this.initialiseLSformElement_mail(el);
    },
    
    onBtnClick: function(btn) {
      var href = btn.getParent().getFirst().href;
      if (typeof(href)=="undefined") {
        href = 'mailto:'+btn.getParent().getFirst().value;
      }
      if (href!="") {
        location.href = href;
      }
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_mail = new LSformElement_mail();
});
