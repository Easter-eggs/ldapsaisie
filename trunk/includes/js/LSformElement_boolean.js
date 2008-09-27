var LSformElement_boolean = new Class({
    initialize: function(){
      this.initialiseLSformElement_boolean();
    },
    
    initialiseLSformElement_boolean: function() {
      $$('li.LSformElement_boolean').each(function(el) {
        var btn = new Element('img');
        btn.setProperties({
          src:    'templates/images/clear.png',
          alt:    'Reset',
          title:  'Reset'
        });
        btn.addClass('btn');
        btn.setStyle('vertical-align','top');
        btn.addEvent('click',this.onClearBtnClick.bind(this,btn));
        btn.injectInside(el);
      }, this);
    },
    
    onClearBtnClick: function(btn) {
      var li = btn.getParent();
      li.getElements('input').each(function(input) {
        input.checked=false;
      },this);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_boolean = new LSformElement_boolean();
});
