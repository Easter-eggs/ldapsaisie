var LSformElement_supannCompositeAttribute = new Class({
    initialize: function(){
      this.fields = [];
      this.initialiseLSformElement_supannCompositeAttribute();
    },
    
    initialiseLSformElement_supannCompositeAttribute: function(el) {
      if (!$type(el)) {
        el = document;
      }
      el.getElements('ul.LSformElement_supannCompositeAttribute').each(function(ul) {
        this.fields[ul.id] = new LSformElement_supannCompositeAttribute_field(ul);
      }, this);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_supannCompositeAttribute = new LSformElement_supannCompositeAttribute();
});
