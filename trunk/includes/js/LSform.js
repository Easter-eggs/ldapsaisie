var LSform = new Class({
    initialize: function(){
      this._modules=[];
      this._elements=[];
      
      this.objecttype = $('LSform_objecttype').value,
      this.objectdn = $('LSform_objectdn').value,
      this.idform = $('LSform_idform').value,
      
      this.initializeLSform();
    },
    
    initializeLSform: function(el) {
      this.LStips = new Tips('.LStips');
      if (typeof(el) == 'undefined') {
        el = document;
      }
      el.getElements('ul.LSform').each(function(ul) {
        this._elements[ul.id] = new LSformElement(this,ul.id,ul);
      }, this);
    },
    
    addModule: function(name,obj) {
      this._modules[name]=obj;
    },
    
    initializeModule: function(fieldType,li) {
      if ($type(this._modules[fieldType])) {
        try {
          this._modules[fieldType].reinitialize(li);
        }
        catch(e) {
          LSdebug('Pas de reinitialise pour ' + fieldType);
        }
      }
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSform = new LSform();
});
