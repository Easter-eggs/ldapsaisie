var LSform = new Class({
    initialize: function(){
      $$('img.LSform-add-field-btn').each(function(el) {
        el.addEvent('click',this.onAddFieldBtnClick.bind(this,el));
      }, this);

      $$('img.LSform-remove-field-btn').each(function(el) {
        el.addEvent('click',this.onRemoveFieldBtnClick.bind(this,el));
      }, this);
    },
    
    onAddFieldBtnClick: function(img){
      var getAttrName = /LSform_add_field_btn_(.*)_.*/
      var attrName = getAttrName.exec(img.id)[1];
      LSdebug(attrName);

      var data = {
        template:   'LSform',
        action:     'onAddFieldBtnClick',
        attribute:  attrName,
        objecttype: $('LSform_objecttype').value,
        objectdn:   $('LSform_objectdn').value,
        idform:     $('LSform_idform').value,
        img:        img.id
      };
      LSdebug(data);
      data.imgload = varLSdefault.loadingImgDisplay(img);
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onAddFieldBtnClickComplete.bind(this)}).send();
    },

    onAddFieldBtnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      LSdebug(data);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        var li = new Element('li');
        var img = $(data.img);
        li.set('html',data.html);
        li.injectAfter(img.getParent());
        li.getElements('img[class=LSform-add-field-btn]').each(function(el) {
          el.addEvent('click',this.onAddFieldBtnClick.bind(this,el));
        }, this);
        li.getElements('img[class=LSform-remove-field-btn]').each(function(el) {
          el.addEvent('click',this.onRemoveFieldBtnClick.bind(this,el));
        }, this);
      }
    },

    onRemoveFieldBtnClick: function(img) {
      if (img.getParent().getParent().getChildren().length == 1) {
        img.getPrevious().getPrevious().value='';
      }
      else {
        img.getParent().destroy();
      }
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSform = new LSform();
});
