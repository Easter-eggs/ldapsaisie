var LSformElement_select = new Class({
    initialize: function(){
      this.initialiseLSformElement_select();
    },
    
    initialiseLSformElement_select: function() {
      $$('select.LSform').each(function(el) {
        var btn = new Element('img');
        btn.setProperties({
          src:    varLSdefault.imagePath('clear.png'),
          alt:    'Reset',
          title:  'Reset'
        });
        btn.addClass('btn');
        btn.setStyle('vertical-align','top');
        btn.addEvent('click',this.onClearBtnClick.bind(this,btn));
        btn.injectAfter(el);
      }, this);
    },
    
    onClearBtnClick: function(btn) {
      var select = btn.getPrevious();
      this.resetSelect(select);
    },
    
    resetSelect: function(select) {
      for(var i=0;i<select.length;i++) {
        select[i].selected=false;
      }     
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_select = new LSformElement_select();
});
