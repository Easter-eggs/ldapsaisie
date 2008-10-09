var LSformElement_password = new Class({
    initialize: function(){
      this.LSformElement_password_generate_inputHistory = [];
      $$('img.LSformElement_password_generate_btn').each(function(el) {
        el.addEvent('click',this.onLSformElement_password_generate_btnClick.bind(this,el));
      }, this);
      
      $$('img.LSformElement_password_view_btn').each(function(el) {
        el.addEvent('click',this.onLSformElement_password_view_btnClick.bind(this,el));
      }, this);
      
      this.LSformElement_password_background_color = [];
      
      $$('img.LSformElement_password_verify_btn').each(function(el) {
        el.addEvent('click',this.onLSformElement_password_verify_btnClick.bind(this,el));
      }, this);
      this.initialiseLSformElement_password_generate();
    },
    
    initialiseLSformElement_password_generate: function() {
      $$('input.LSformElement_password_generate').each(function(el) {
        this.LSformElement_password_background_color[el.id] = el.getStyle('background-color');
        el.addEvent('click',this.onLSformElement_password_verify_inputClick.bind(this,el));
        el.addEvent('keyup',this.onLSformElement_password_generate_inputKeyUp.bind(this,el));
      }, this);
    },
    
    onLSformElement_password_generate_btnClick: function(img) {
      var getAttrNameAndId = /LSformElement_password_generate_btn_(.*)_([0-9]*)/
      var getAttrNameAndIdValues = getAttrNameAndId.exec(img.id);
      var attrName = getAttrNameAndIdValues[1];
      var fieldId = 'LSformElement_password_' + attrName + '_' + getAttrNameAndIdValues[2];
      var viewBtnId = 'LSformElement_password_view_btn_' + attrName + '_' + getAttrNameAndIdValues[2];

      var data = {
        template:   'LSform',
        action:     'generatePassword',
        attribute:  attrName,
        objecttype: $('LSform_objecttype').value,
        idform:     $('LSform_idform').value,
        viewBtnId:  viewBtnId,
        fieldId:    fieldId
      };
      data.imgload=varLSdefault.loadingImgDisplay(img);
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onLSformElement_password_generate_btnClickComplete.bind(this)}).send();
    },
    
    onLSformElement_password_generate_btnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        this.changeInputType($(data.fieldId),'text');
        $(data.fieldId).value=data.generatePassword;
        $(data.viewBtnId).setProperty('src',varLSdefault.imagePath('hide.png'));
        this.LSformElement_password_generate_inputHistory[data.fieldId]=data.generatePassword;
      }
    },

    onLSformElement_password_generate_inputKeyUp: function(input) {
      if (input.type=='text') {
        if((this.LSformElement_password_generate_inputHistory[input.id]!=input.value)&&(typeof(this.LSformElement_password_generate_inputHistory[input.id])!='undefined')&&(this.LSformElement_password_generate_inputHistory[input.id]!='')) {
          this.onLSformElement_password_generate_inputModify(input);
        }
      }
    },
    
    onLSformElement_password_generate_inputModify: function(input) {
      input.value='';
      input = this.changeInputType(input,'password');
      var getAttrNameAndId = /LSformElement_password_(.*)_([0-9]*)/
      var attrNameAndId = getAttrNameAndId.exec(input.id);
      var viewBtnId = 'LSformElement_password_view_btn_' + attrNameAndId[1] + '_' + attrNameAndId[2];
      $(viewBtnId).setProperty('src',varLSdefault.imagePath('view.png'));
      this.LSformElement_password_generate_inputHistory[input.id]='';
      input.focus();
    },
    
    onLSformElement_password_view_btnClick: function(img) {
      var getAttrNameAndId = /LSformElement_password_view_btn_(.*)_([0-9]*)/
      var getAttrNameAndIdValues = getAttrNameAndId.exec(img.id);
      var attrName = getAttrNameAndIdValues[1];
      var fieldId = 'LSformElement_password_' + attrName + '_' + getAttrNameAndIdValues[2];
      
      input = $(fieldId);
      
      if (input.type=='password') {
        input = this.changeInputType(input,'text');
        img.setProperty('src',varLSdefault.imagePath('hide.png'));
      }
      else {
        input = this.changeInputType(input,'password');
        img.setProperty('src',varLSdefault.imagePath('view.png'));
      }
      input.focus();
    },
    
    changeInputType: function(input,newType) {
      var newInput = new Element('input');
      newInput.setProperty('name',input.getProperty('name'));
      newInput.setProperty('type',newType);
      newInput.setProperty('class',input.getProperty('class'));
      newInput.setProperty('id',input.getProperty('id'));
      newInput.setProperty('value',input.getProperty('value'));
      newInput.injectAfter(input);
      input.destroy();
      this.initialiseLSformElement_password_generate();
      return newInput;
    },
    
    onLSformElement_password_verify_btnClick: function(img) {
      var getAttrNameAndId = /LSformElement_password_verify_btn_(.*)_([0-9]*)/
      var getAttrNameAndIdValues = getAttrNameAndId.exec(img.id);
      var attrName = getAttrNameAndIdValues[1];
      var fieldId = 'LSformElement_password_' + attrName + '_' + getAttrNameAndIdValues[2];
      var verifyBtnId = 'LSformElement_password_verify_btn_' + attrName + '_' + getAttrNameAndIdValues[2];

      var data = {
        template:   'LSform',
        action:     'verifyPassword',
        attribute:  attrName,
        objecttype: $('LSform_objecttype').value,
        idform:     $('LSform_idform').value,
        fieldId:    fieldId,
        fieldValue: $(fieldId).value,
        objectdn:   $('LSform_objectdn').value
      };
      LSdebug(data);
      data.imgload=varLSdefault.loadingImgDisplay(img);
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onLSformElement_password_verify_btnClickComplete.bind(this)}).send();
    },
    
    onLSformElement_password_verify_btnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        if (data.verifyPassword) {
          // ok
          $(data.fieldId).setStyle('background-color','#73F386');
        }
        else {
          // nok
          $(data.fieldId).setStyle('background-color','#f59a67');
        }
      }
    },
    
    onLSformElement_password_verify_inputClick: function(input) {
      input.setStyle('background-color',this.LSformElement_password_background_color[input.id]);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_password = new LSformElement_password();
});
