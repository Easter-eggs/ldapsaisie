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
      var getName = /^(.*)\[[0-9]*\]$/;
      el.getElements('input.LSformElement_date[type=text]').each(function(input) {
        var name = getName.exec(input.name)[1];
        this.fields[name] = new LSformElement_date_field(name,input);
        varLSform.addField(name, this.fields[name]);
      }, this);
    },

    reinitialize: function(el) {
      this.initialiseLSformElement_date(el);
    },

    clearValue: function() {
      this.fields.each(function(field) {
        field.clearValue();
      }, this);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_date = new LSformElement_date();
});
