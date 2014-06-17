var LSformElement_text = new Class({
    initialize: function(){
      this.elements =  new Hash();
      this.initialiseLSformElement_text();
      if ($type(varLSform)) {
        varLSform.addModule("LSformElement_text",this);
      }
    },
    
    initialiseLSformElement_text: function(el) {
      
      if (typeof(el) == 'undefined') {
        el = document;
      }
      var getName = /^(.*)\[\]$/
      el.getElements('input.LSformElement_text').each(function(input) {
        var name = getName.exec(input.name)[1];
        if (!$type(this.elements[name])) {
          this.elements[name] = new Hash();
        }
        var id = this.elements[name].getLength(); 
        this.elements[name][id] = new LSformElement_text_field(name,input,this);
      }, this);
      this.elements.each(function(element) {
        element.each(function(field) {
          field.start.bind(field)();
        },this);
      },this);
    },
    
    reinitialize: function(el) {
      this.initialiseLSformElement_text(el);
    },
    
    getDependsFields: function(format) {
      var retval=new Array();
      var find = 1;
      var getMotif =  new RegExp('%\{(([A-Za-z0-9]+)(\:(-?[0-9])+)?(\:(-?[0-9])+)?)\}');
      var ch = null;
      while (find) {
        ch = getMotif.exec(format);
        if ($type(ch)) {
          retval.include(ch[2]);
          format=format.replace (
                  new RegExp('%\{'+ch[1]+'\}'),
                  ''
                );
        }
        else {
          find=0;
        }           
      }
      return retval;
    }
    
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_text = new LSformElement_text();
});
