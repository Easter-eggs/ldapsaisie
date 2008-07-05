var LSview = new Class({
    initialize: function(){
      $$('td.LSobject-list-names').each(function(el) {
        el.addEvent('click',this.onTdLSobjectListNamesClick.bind(this,el));
      }, this);
      $$('td.LSobject-list-names').each(function(el) {
        el.addEvent('mouseenter',this.onTdLSobjectListNamesOver.bind(this,el));
      }, this);
      $$('td.LSobject-list-names').each(function(el) {
        el.addEvent('mouseleave',this.onTdLSobjectListNamesOut.bind(this,el));
      }, this);
    },

    onTdLSobjectListNamesClick: function(td) {
      window.location=td.getFirst().href;
    },

    onTdLSobjectListNamesOver: function(td){
      td.imgEdit = new Element('img');
      td.imgEdit.src = 'templates/images/view.png';
      td.imgEdit.injectInside(td);
    },
    
    onTdLSobjectListNamesOut: function(td) {
      td.imgEdit.destroy();
    }

});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSview = new LSview();
});
