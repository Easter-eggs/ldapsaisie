var LSformElement_password_field = new Class({
    initialize: function(name,input){
      this.name = name;
      this.input = input;
      this.params = varLSdefault.getParams(this.name);
      LSdebug(this.params);
      this.initialiseLSformElement_password_field();
    },
    
    initialiseLSformElement_password_field: function() {
      // ViewBtn
      this.viewBtn = new Element('img');
      this.viewBtn.src = varLSdefault.imagePath('view.png');
      this.viewBtn.addClass('btn');
      this.viewBtn.addEvent('click',this.changeInputType.bind(this));
      this.viewBtn.injectAfter(this.input);
      
      // Verify
      if (this.params['verify']) {
        this.bgColor = this.input.getStyle('background-color');
        this.verifyFx = new Fx.Tween(this.input,{property: 'background-color',duration:600});
        this.verifyBtn = new Element('img');
        this.verifyBtn.src = varLSdefault.imagePath('verify.png');
        this.verifyBtn.addClass('btn');
        this.verifyBtn.addEvent('click',this.onVerifyBtnClick.bind(this));
        this.verifyBtn.injectAfter(this.input);
      }
      
      if (this.params['generate']) {
        this.generateBtn = new Element('img');
        this.generateBtn.src = varLSdefault.imagePath('generate.png');
        this.generateBtn.addClass('btn');
        this.generateBtn.addEvent('click',this.onGenerateBtnClick.bind(this));
        this.generateBtn.injectAfter(this.input);
      }
      
      this.initialize_input();
    },
    
    initialize_input: function() {
      // Verify
      if (this.params['verify']) {
        this.verifyFx = new Fx.Tween(this.input,{property: 'background-color',duration:600});
      }
    },
    
    onGenerateBtnClick: function() {
      var data = {
        template:   'LSform',
        action:     'generatePassword',
        attribute:  this.name,
        objecttype: varLSform.objecttype,
        idform:     varLSform.idform
      };
      data.imgload=varLSdefault.loadingImgDisplay(this.generateBtn);
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onGenerateBtnClickComplete.bind(this)}).send();
    },
    
    onGenerateBtnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        this.input.value=data.generatePassword;
        this.changeInputType('view');
      }
    },

    changeInputType: function(state) {
      if (((this.input.type=='password')&&(state=='hide'))||((this.input.type=='text')&&(state=='view'))) {
        return this.input;
      }
      if (this.input.type=='password') {
        var newType = 'text';
        this.viewBtn.src=varLSdefault.imagePath('hide.png');
      }
      else {
        var newType = 'password';
        this.viewBtn.src=varLSdefault.imagePath('view.png');
      }
      var newInput = new Element('input');
      newInput.setProperty('name',this.input.getProperty('name'));
      newInput.setProperty('type',newType);
      newInput.setProperty('class',this.input.getProperty('class'));
      newInput.setProperty('value',this.input.getProperty('value'));
      newInput.injectAfter(this.input);
      this.input.destroy();
      this.input = newInput;
      this.initialize_input();
      return newInput;
    },
    
    onVerifyBtnClick: function() {
      var data = {
        template:   'LSform',
        action:     'verifyPassword',
        attribute:  this.name,
        objecttype: varLSform.objecttype,
        idform:     varLSform.idform,
        objectdn:   varLSform.objectdn,
        fieldValue: this.input.value
      };
      LSdebug(data);
      data.imgload=varLSdefault.loadingImgDisplay(this.verifyBtn);
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onVerifyBtnClickComplete.bind(this)}).send();
    },
    
    onVerifyBtnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        if (data.verifyPassword) {
          // ok
          this.verifyFx.start('#73F386');
        }
        else {
          // nok
          this.verifyFx.start('#f59a67');
        }
        (function(){this.verifyFx.start(this.bgColor);}).delay(1000, this);
      }
    }
});
