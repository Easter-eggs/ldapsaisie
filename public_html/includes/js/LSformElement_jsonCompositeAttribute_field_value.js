var LSformElement_jsonCompositeAttribute_field_value = new Class({
    initialize: function(li,name,field_type){
        this.li=li;
        this.name = name;
        this.input_uuid = li.getElement('input[name='+name+'__values_uuid[]]');
        this.uuid = this.input_uuid.get('value');
        this.components = {};
        this.field_type = field_type;
        this.initializeLSformElement_jsonCompositeAttribute_field_value();
        varLSform.addModule(field_type,this);
    },

    initializeLSformElement_jsonCompositeAttribute_field_value: function(el) {
        if (!$type(el)) {
            el = this.li;
        }
        el.getElements('div').each(function(div) {
            this.components[div.get('data-component')]=new LSformElement_jsonCompositeAttribute_field_value_component(div,div.get('data-component'),this.name,this.uuid);
        }, this);
    },

    reinitialize: function(el) {
        this.initializeLSformElement_jsonCompositeAttribute_field_value(el);
    },

    clear: function() {
        for (c in this.components) {
            this.components[c].clear();
        }
    }
});
