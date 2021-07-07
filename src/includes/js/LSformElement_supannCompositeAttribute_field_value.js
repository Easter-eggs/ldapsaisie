var LSformElement_supannCompositeAttribute_field_value = new Class({
  initialize: function(li,name,field_type){
    this.li=li;
    this.name = name;
    this.components = {};
    this.field_type = field_type;
    this.initializeLSformElement_supannCompositeAttribute_field_value();
    varLSform.addModule(field_type,this);
  },

  initializeLSformElement_supannCompositeAttribute_field_value: function(el) {
    if (!$type(el)) {
      el = this.li;
    }
    el.getElements('p').each(function(p) {
      this.components[p.get('data-component')]=new LSformElement_supannCompositeAttribute_field_value_component(p,p.get('data-component'),this.name);
    }, this);
  },

  reinitialize: function(el) {
    this.initializeLSformElement_supannCompositeAttribute_field_value(el);
  },

  clear: function() {
    for (c in this.components) {
      this.components[c].clear();
    }
  }
});
