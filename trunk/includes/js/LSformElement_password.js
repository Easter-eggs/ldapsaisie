var LSformElement_password = new Class({
    initialize: function(){
      this.fields=new Hash();
      this.initialiseLSformElement_password();
    },
    
    initialiseLSformElement_password: function() {
      var getName = /^(.*)\[\]$/
      $$('input.LSformElement_password').each(function(input) {
        var name = getName.exec(input.name)[1];
        this.fields[name] = new LSformElement_password_field(name,input);
      }, this);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_password = new LSformElement_password();
});
