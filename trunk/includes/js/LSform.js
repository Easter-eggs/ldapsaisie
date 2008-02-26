var LSform = new Class({
    initialize: function(){
      $$('img.LSform-add-field-btn').each(function(el) {
        el.addEvent('click',this.onAddFieldBtnClick.bind(this,el));
      }, this);

      $$('img.LSform-remove-field-btn').each(function(el) {
        el.addEvent('click',this.onRemoveFieldBtnClick.bind(this,el));
      }, this);
      
      $$('div.LSform_image').each(function(el) {
        el.addEvent('mouseenter',this.onMouseEnterImage.bind(this));
      }, this);
      
      $$('div.LSform_image').each(function(el) {
        el.addEvent('mouseleave',this.onMouseLeaveImage.bind(this));
      }, this);
      
      $$('img.LSform_image_action_zoom').each(function(el) {
        el.addEvent('click',this.zoomImg.bindWithEvent(this,el.getParent().getParent().getNext().src));
      }, this);
      
      $$('img.LSform_image_action_delete').each(function(el) {
        el.addEvent('click',this.onImageDeleteBtnClick.bind(this,el));
      }, this);
      
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
    
    zoomImg: function(event, src) {
      new Event(event).stop();
      varLSsmoothbox.openImg(src);
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
      varLSdefault.loadingImgDisplay(img);
      new Ajax('index_ajax.php',  {data: data, onComplete: this.onAddFieldBtnClickComplete.bind(this)}).request();
    },

    onAddFieldBtnClickComplete: function(responseText, responseXML) {
      varLSdefault.loadingImgHide();
      var data = Json.evaluate(responseText);
      LSdebug(data);
      if ( data ) {
        if ( typeof(data.LSerror) != "undefined" ) {
            varLSdefault.displayError(data.LSerror);
            return;
          } 
          else {  
            var li = new Element('li');
            var img = $(data.img);
            li.setHTML(data.html);
            li.injectAfter(img.getParent());
            li.getElements('img[class=LSform-add-field-btn]').each(function(el) {
              el.addEvent('click',this.onAddFieldBtnClick.bind(this,el));
            }, this);
            li.getElements('img[class=LSform-remove-field-btn]').each(function(el) {
              el.addEvent('click',this.onRemoveFieldBtnClick.bind(this,el));
            }, this);
          }
      }
    },

    onRemoveFieldBtnClick: function(img) {
      if (img.getParent().getParent().getChildren().length == 1) {
        img.getPrevious().getPrevious().value='';
      }
      else {
        img.getParent().remove();
      }
    },
    
    onMouseEnterImage: function() {
      $$('ul.LSform_image_actions').each(function(el) {
        el.setStyle('visibility','visible');  
      }, this);
    },
    
    onMouseLeaveImage: function() {
      $$('ul.LSform_image_actions').each(function(el) {
        el.setStyle('visibility','hidden');  
      }, this);
    },
    
    onImageDeleteBtnClick: function(img) {
      $$('form.LSform').each(function(el) {
        var input = new Element('input');
        input.type = 'hidden';
        var getInputId = /LSform_image_action_delete_(.*)/
        input.name = $(getInputId.exec(img.id)[1]).name + '_delete';
        input.value='delete';
        input.injectInside(el);  
      },this);
      img.getParent().getParent().getParent().remove();
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
        values:     Json.toString(values),
        href:       a.href
      };
      
      LSdebug(data);
      
      data.imgload=varLSdefault.loadingImgDisplay(a,'inside');
      this.refreshFields=fieldId;
      new Ajax('index_ajax.php',  {data: data, onComplete: this.onLSformElement_select_object_addBtnClickComplete.bind(this)}).request();
    },
    
    onLSformElement_select_object_addBtnClickComplete: function(responseText, responseXML) {
      var data = Json.evaluate(responseText);
      LSdebug(data);
      if ( data ) {
        if ( typeof(data.LSerror) != "undefined" ) {
            varLSdefault.displayError(data.LSerror);
            return;
          } 
          else {
            varLSdefault.loadingImgHide(data.imgload);
            varLSsmoothbox.openURL(data.href,this);
          }
      }
    },
    
    refresh: function() {
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
      LSdebug(data);
      data.imgload=varLSdefault.loadingImgDisplay($('a_' + this.refreshFields));
      new Ajax('index_ajax.php',  {data: data, onComplete: this.onRefreshComplete.bind(this)}).request();
    },
    
    onRefreshComplete: function(responseText, responseXML) {
      var data = Json.evaluate(responseText);
      if ( data ) {
        if ( typeof(data.LSerror) != "undefined" ) {
          varLSdefault.loadingImgHide();
          varLSdefault.displayError(data.LSerror);
          return;
        } 
        else {  
          varLSdefault.loadingImgHide(data.imgload);
          $(this.refreshFields).getParent().setHTML(data.html);
          this.initialiseLSformElement_select_object();
        }
      }
    },
    
    LSformElement_select_object_deleteBtn: function(img) {
      img.getParent().remove();
    }

});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSform = new LSform();
});
