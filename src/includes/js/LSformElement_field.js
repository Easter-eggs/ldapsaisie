var LSformElement_field = new Class({
    initialize: function(LSformElement,li,id,field_name){
      this.id = id;
      this.LSformElement = LSformElement;
      this.li = li;
      this.field_name = field_name;

      if (this.LSformElement.multiple) {
        this.addFieldBtn = new Element('img');
        this.addFieldBtn.src = varLSdefault.imagePath('add');
        this.addFieldBtn.addClass('btn');
        this.addFieldBtn.addEvent('click',this.LSformElement.onAddFieldBtnClick.bind(this.LSformElement,this));
        this.addFieldBtn.injectInside(this.li);
        varLSdefault.addHelpInfo(this.addFieldBtn,'LSform','addFieldBtn');

        this.removeFieldBtn = new Element('img');
        this.removeFieldBtn.src = varLSdefault.imagePath('remove');
        this.removeFieldBtn.addClass('btn');
        this.removeFieldBtn.addEvent('click',this.LSformElement.onRemoveFieldBtnClick.bind(this.LSformElement,this));
        this.removeFieldBtn.injectInside(this.li);
        varLSdefault.addHelpInfo(this.removeFieldBtn,'LSform','removeFieldBtn');
      }
    },

    getFormField: function() {
      if ($type(this._formField)) {
        return this._formField;
      }
      this._formField = this.li.getFirst('input');
      if(!$type(this._formField)) {
        this._formField = this.li.getFirst('textarea');
      }
      return this._formField;
    },

    clearValue: function() {
      if ($type(this.getFormField())) {
        this.getFormField().value='';
      }
      varLSform.clearFieldValue(this.field_name);
    },

    remove: function() {
      this.li.destroy();
    }
});
