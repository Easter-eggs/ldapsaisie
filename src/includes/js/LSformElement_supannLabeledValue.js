var LSformElement_supannLabeledValue = new Class({
    initialize: function(){
      this.fields = [];
      this.initialiseLSformElement_supannLabeledValue();
    },

    initialiseLSformElement_supannLabeledValue: function(el) {
      if (!$type(el)) {
        el = document;
      }
      el.getElements('ul.LSformElement_supannLabeledValue').each(function(ul) {
        this.fields[ul.id] = new LSformElement_supannLabeledValue_field(ul);
      }, this);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_supannLabeledValue = new LSformElement_supannLabeledValue();
});
