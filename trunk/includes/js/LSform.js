var LSform = new Class({
    initialize: function(){
      this._modules=[];
      this.initializeLSform_AddAndRemoveBtns();
      this.LStips = new Tips('.LStips');
    },
    
    initializeLSform_AddAndRemoveBtns: function(el) {
      if (typeof(el) == 'undefined') {
        el = document;
      }
      el.getElements('img[class=LSform-add-field-btn]').each(function(btn) {
        btn.addEvent('click',this.onAddFieldBtnClick.bind(this,btn));
      }, this);
      el.getElements('img[class=LSform-remove-field-btn]').each(function(btn) {
        btn.addEvent('click',this.onRemoveFieldBtnClick.bind(this,btn));
      }, this);
    },
    
    addModule: function(name,obj) {
      this._modules[name]=obj;
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
        this.initializeLSform_AddAndRemoveBtns(li);
        if (typeof(this._modules[data.fieldtype]) != "undefined") {
          try {
            this._modules[data.fieldtype].reinitialize(li);
          }
          catch(e) {
            LSdebug('Pas de reinitialise pour ' + data.fieldtype);
          }
        }
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
