var LSformElement_text = new Class({
    initialize: function(){
      this.fields =  new Hash();
      this.initialiseLSformElement_text();
    },
    
    initialiseLSformElement_text: function(el) {
      if (typeof(el) == 'undefined') {
        el = document;
      }
      var getName = /^(.*)\[\]$/
      el.getElements('ul.LSformElement_text').each(function(ul) {
        var first = ul.getElement('input.LSformElement_text');
        if ($type(first)) {
          var name = getName.exec(first.name)[1];
          this.fields[name] = new LSformElement_text_field(name,first,this);
        }
      }, this);
      this.fields.each(function(el) {
        el.start.bind(el)();  
      },this);
    },
    
    getDependsFields: function(format) {
      var retval=new Array();
      var find = 1;
      var getMotif = /%{([A-Za-z0-9]+)}/
      var ch = null;
      while (find) {
        ch = getMotif.exec(format);
        if ($type(ch)) {
          retval.include(ch[1]);
          format=format.replace (
                  new RegExp('%{'+ch[1]+'}'),
                  ''
                );
        }
        else {
          find=0;
        }           
      }
      return retval;
    },
    
    getInput: function(name) {
      return this.fields[name].getInput();
    },
    
    getValue: function(name) {
      return this.fields[name].getValue();
    }
    
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_text = new LSformElement_text();
});
