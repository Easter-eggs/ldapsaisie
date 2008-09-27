var LSformElement_select_object = new Class({
    initialize: function(){
      this.initialiseLSformElement_select_object();
    },
    
    initialiseLSformElement_select_object: function(el) {
      if (!$type(el)) {
        el = document;
      }
      el.getElements('ul.LSformElement_select_object').each(function(ul) {
        var params = varLSdefault.LSjsConfig[ul.id];
        if ($type(params)) {
          if (!params.freeze) {
            // Class du UL
            ul.addClass('LSformElement_select_object_edit');
            
            // Delete btns
            ul.getElements('a.LSformElement_select_object').each(function(a){
              var btn = new Element('img');
              btn.addClass('btn');
              btn.setProperties({
                src:    'templates/images/delete.png',
                alt:    params.deleteBtns.alt
              });
              btn.addEvent('click',this.LSformElement_select_object_deleteBtn.bind(this,btn));
              btn.injectAfter(a);
            },this);
            
            // li
            ul.getElements('li').each(function(li){
              li.addClass('LSformElement_select_object');
            },this);
            
            // Head
            var li = new Element('li');
            li.addClass('LSformElement_select_object_addBtn');
            
            var addBtn = new Element('a');
            addBtn.addClass('LSformElement_select_object');
            addBtn.addClass('LSformElement_select_object_addBtn');
            addBtn.setProperties({
              href:   params.addBtn.href,
              id:     params.addBtn.id
            });
            addBtn.set('html',params.addBtn.label);
            addBtn.addEvent('click',this.onLSformElement_select_object_addBtnClick.bindWithEvent(this,addBtn));
            addBtn.injectInside(li);
            
            var input = new Element('input');
            input.setProperties({
              type:     'hidden',
              name:     params.inputHidden.name,
              id:       params.inputHidden.id,
              value:    params.inputHidden.value,
            });
            input.injectInside(li);
            li.inject(ul,'top');
            

          }
        }
        
      }, this);
    },
    
    onLSformElement_select_object_addBtnClick: function(event,a) {
      new Event(event).stop();
      var getAttrName = /a_LSformElement_select_object_(.*)/
      var attrName = getAttrName.exec(a.id)[1];
      var fieldId = 'LSformElement_select_object_'+attrName;
      
      values = new Array();
      a.getParent().getParent().getElements('input.LSformElement_select_object').each(function(el) {
        values.push(el.getProperty('value'));
      }, this);
      
      var data = {
        template:   'LSselect',
        action:     'refreshSession',
        objecttype: $('LSformElement_select_object_objecttype_'+attrName).value,
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
      var getAttrName = /LSformElement_select_object_(.*)/
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
        var dd = $(this.refreshFields).getParent();
        dd.set('html',data.html);
        this.initialiseLSformElement_select_object(dd);
      }
    },
    
    LSformElement_select_object_deleteBtn: function(img) {
      img.getParent().destroy();
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_select_object = new LSformElement_select_object();
});
