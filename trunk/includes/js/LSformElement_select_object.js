var LSformElement_select_object = new Class({
    initialize: function(){
      this.fields = [];
      this.initialiseLSformElement_select_object();
    },
    
    initialiseLSformElement_select_object: function(el) {
      if (!$type(el)) {
        el = document;
      }
      el.getElements('ul.LSformElement_select_object').each(function(ul) {
        this.fields[ul.id] = new LSformElement_select_object_field(ul);
      }, this);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_select_object = new LSformElement_select_object();
});
