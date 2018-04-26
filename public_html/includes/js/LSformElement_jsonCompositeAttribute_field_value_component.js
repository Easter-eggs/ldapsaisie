var LSformElement_jsonCompositeAttribute_field_value_component = new Class({
    initialize: function(div,name,field_name,field_uuid){
      this.div = div;

      this.field_name = field_name;
      this.field_uuid = field_uuid;
      this.field_params = varLSdefault.LSjsConfig[this.field_name];

      this.name = name;
      this.params = this.field_params['components'][this.name];
      
      this.label = div.getElement('label');
      if (this.params.type == 'select_list') {
        this.select = div.getElement('select');
      }
      else {
        // Type text
        this.ul = div.getElement('ul');
        this.lis = {};
        this.values = {};
        this.ul.getElements('li').each(function(li) {
          this.initTextComponentValue(li);
        }, this);
      }
    },

    initTextComponentValue: function(li) {
      var uuid = generate_uuid();
      this.lis[uuid] = li;
      this.values[uuid] = new LSformElement_jsonCompositeAttribute_field_value_component_text_value(this,li);
    },

    onAddTextValueBtnClick: function(after) {
      var li = new Element('li');
      var input = new Element('input');
      input.type='text';
      input.name=this.field_name+'__'+this.name+'__'+this.field_uuid+'[]';
      input.injectInside(li);
      li.injectAfter(after.li);
      this.initTextComponentValue(li);
    },

    onRemoveTextValueBtnClick: function(value) {
      if (this.ul.getElements('li').length == 1) {
        value.clear.bind(value)();
      }
      else {
        value.remove.bind(value)();
      }
    },
    
    clear: function() {
      if (this.params.type == 'select_list') {
        this.select.selectedIndex=-1;
      }
      else {
        this.values.each(function(value) {
          value.clear();
        }, this);
      }
    }
});
