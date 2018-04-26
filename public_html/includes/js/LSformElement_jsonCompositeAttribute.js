var LSformElement_jsonCompositeAttribute = new Class({
    initialize: function(){
      this.fields = [];
      this.initialiseLSformElement_jsonCompositeAttribute();
    },
    
    initialiseLSformElement_jsonCompositeAttribute: function(el) {
      if (!$type(el)) {
        el = document;
      }
      el.getElements('ul.LSformElement_jsonCompositeAttribute').each(function(ul) {
        this.fields[ul.id] = new LSformElement_jsonCompositeAttribute_field(ul);
      }, this);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_jsonCompositeAttribute = new LSformElement_jsonCompositeAttribute();
});
