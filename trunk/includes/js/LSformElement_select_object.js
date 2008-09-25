var LSformElement_select_object = new Class({
    initialize: function(){
      this.initialiseLSformElement_select_object();
    },
    
    initialiseLSformElement_select_object: function() {
      $$('a.LSformElement_select_object_addBtn').each(function(el) {
        el.addEvent('click',this.onLSformElement_select_object_addBtnClick.bindWithEvent(this,el));
      }, this);
      
      $$('img.LSformElement_select_object_deleteBtn').each(function(el) {
        el.addEvent('click',this.LSformElement_select_object_deleteBtn.bind(this,el));
      }, this);
    },
    
    onLSformElement_select_object_addBtnClick: function(event,a) {
      new Event(event).stop();
      var getFieldId = /a_(.*)/
      var fieldId = getFieldId.exec(a.id)[1];
      var getId = /a_LSformElement_select_object_.*_([0-9]*)$/
      var Id = getId.exec(a.id)[1];
      
      values = new Array();
      $$('input.LSformElement_select_object').each(function(el) {
        values.push(el.getProperty('value'));
      }, this);
      
      var data = {
        template:   'LSselect',
        action:     'refreshSession',
        objecttype: $('LSformElement_select_object_objecttype_'+Id).value,
        values:     JSON.encode(values),
        href:       a.href
      };
      
      data.imgload=varLSdefault.loadingImgDisplay(a,'inside');
      this.refreshFields=fieldId;
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onLSformElement_select_object_addBtnClickComplete.bind(this)}).send();
    },
    
    onLSformElement_select_object_addBtnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        varLSsmoothbox.asNew();
        varLSsmoothbox.addEvent('valid',this.onLSsmoothboxValid.bind(this));
        varLSsmoothbox.displayValidBtn();
        varLSsmoothbox.openURL(data.href,{width: 615});
      }
    },
    
    onLSsmoothboxValid: function() {
      var getAttrName = /LSformElement_select_object_(.*)_[0-9]*/
      var attrName = getAttrName.exec(this.refreshFields)[1];
      var data = {
        template:   'LSform',
        action:     'refreshField',
        attribute:  attrName,
        objecttype: $('LSform_objecttype').value,
        objectdn:   $('LSform_objectdn').value,
        idform:     $('LSform_idform').value,
        ul:         this.refreshFields
      };
      data.imgload=varLSdefault.loadingImgDisplay($('a_' + this.refreshFields));
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onLSsmoothboxValidComplete.bind(this)}).send();
    },
    
    onLSsmoothboxValidComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        $(this.refreshFields).getParent().set('html',data.html);
        this.initialiseLSformElement_select_object();
      }
    },
    
    LSformElement_select_object_deleteBtn: function(img) {
      img.getParent().destroy();
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_select_object = new LSformElement_select_object();
});
