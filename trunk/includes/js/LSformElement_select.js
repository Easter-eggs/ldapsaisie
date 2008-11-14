var LSformElement_select = new Class({
    initialize: function(){
      this.initialiseLSformElement_select();
    },
    
    initialiseLSformElement_select: function() {
      $$('select.LSformElement_select').each(function(el) {
        var btn = new Element('img');
        btn.setProperties({
          src:    varLSdefault.imagePath('clear.png'),
          alt:    'Reset',
          title:  'Reset'
        });
        btn.addClass('btn');
        btn.setStyle('vertical-align','top');
        btn.addEvent('click',this.onClearBtnClick.bind(this,el));
        btn.injectAfter(el);
        varLSdefault.addHelpInfo(btn,'LSformElement_select','clear');
      }, this);
    },
    
    onClearBtnClick: function(select) {
      this.resetSelect(select);
    },
    
    resetSelect: function(select) {
      select.selectedIndex=-1;
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_select = new LSformElement_select();
});
