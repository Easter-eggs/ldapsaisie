var LSformElement_date = new Class({
    initialize: function(){
      this.fields = [];
      this.initialiseLSformElement_date();
      if ($type(varLSform)) {
        varLSform.addModule("LSformElement_date",this);
      }
    },

    initialiseLSformElement_date: function(el) {
      if (!$type(el)) {
        el = document;
      }
      var getName = /^(.*)\[\]$/
      el.getElements('input.LSformElement_date').each(function(input) {
        var name = getName.exec(input.name)[1];
        this.fields[name] = new LSformElement_date_field(name,input);
      }, this);
    },

    reinitialize: function(el) {
      this.initialiseLSformElement_date(el);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_date = new LSformElement_date();
});
