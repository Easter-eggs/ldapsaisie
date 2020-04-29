var LSformElement_supannLabeledValue_field = new Class({
    initialize: function(ul){
		this.ul=ul;
		this.dd=ul.getParent();
		this.name = ul.id;
		this.values = [];
		this.field_type = ul.get('data-fieldType');
		this.initializeLSformElement_supannLabeledValue_field();
		varLSform.addField(this.name,this);
		console.log(this.field_type);
		varLSform.addModule(this.field_type,this);
    },

    initializeLSformElement_supannLabeledValue_field: function(el) {
		if (!$type(el)) {
			el = this.ul;
		}
		el.getElements('li').each(function(li) {
			this.values.push(new LSformElement_supannLabeledValue_field_value(li,this.name,this.field_type));
		}, this);
    },

    clearValue: function() {
		if (this.values.length>1) {
			for(var i=1; i<=this.values.length; i++) {
				$(this.values[i].li).dispose();
			}
			this.values[0].clear();
		}
		else if (this.values.length==1) {
			this.values[0].clear();
		}
	},

    reinitialize: function(li) {
      this.values.push(new LSformElement_supannLabeledValue_field_value(li,this.name,this.field_type));
    }
});
