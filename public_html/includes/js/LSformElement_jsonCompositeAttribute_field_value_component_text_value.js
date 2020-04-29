var LSformElement_jsonCompositeAttribute_field_value_component_text_value = new Class({
    initialize: function(component,li) {
      this.component = component;
      this.li = li;

      this.input = li.getElement('input');

      if (this.component.params.multiple) {
        this.addValueBtn = new Element('img');
        this.addValueBtn.src = varLSdefault.imagePath('add');
        this.addValueBtn.addClass('btn');
        this.addValueBtn.addEvent('click',this.component.onAddTextValueBtnClick.bind(this.component,this));
        this.addValueBtn.injectInside(this.li);

        this.removeValueBtn = new Element('img');
        this.removeValueBtn.src = varLSdefault.imagePath('remove');
        this.removeValueBtn.addClass('btn');
        this.removeValueBtn.addEvent('click',this.component.onRemoveTextValueBtnClick.bind(this.component,this));
        this.removeValueBtn.injectInside(this.li);
      }
    },

    clear: function() {
      this.input.value = '';
    },

    remove: function() {
      this.li.destroy();
    }
});
