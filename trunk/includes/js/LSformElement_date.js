var LSformElement_date = new Class({
    initialize: function(){
      this.fields = [];
      this.initialiseLSformElement_date();
      if (typeof(varLSform) != "undefined") {
        varLSform.addModule("LSformElement_date",this);
      }
    },
    
    initialiseLSformElement_date: function(el) {
      if (typeof(el) == 'undefined') {
        el = document;
      }
      el.getElements('img.LSformElement_date_calendar_btn').each(function(btn) {
        this.fields[btn.id] = new LSformElement_date_field(btn);
      }, this);
    },
    
    reinitialize: function(el) {
      this.initialiseLSformElement_date(el);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_date = new LSformElement_date();
});
