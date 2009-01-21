var LSformElement_maildir = new Class({
    initialize: function(){
      this.fields=new Hash();
      this.initialiseLSformElement_maildir();
    },
    
    initialiseLSformElement_maildir: function() {
      var getName = /^(.*)\[\]$/
      $$('input.LSformElement_maildir').each(function(input) {
        var name = getName.exec(input.name)[1];
        this.fields[name] = new LSformElement_maildir_field(name,input);
      }, this);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_maildir = new LSformElement_maildir();
});
