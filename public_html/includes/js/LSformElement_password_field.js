var LSformElement_password_field = new Class({
    initialize: function(name,input){
      this.name = name;
      this.input = input;
      this.params = varLSdefault.getParams(this.name);
      this.initialiseLSformElement_password_field();
    },
    
    initialiseLSformElement_password_field: function() {
      // ViewHashBtn
      if (this.params['viewHash'] && varLSform.objectdn!= "") {
        this.viewHashBtn = new Element('img');
        this.viewHashBtn.src = varLSdefault.imagePath('view_hash.png');
        this.viewHashBtn.addClass('btn');
        this.viewHashBtn.addEvent('click',this.onViewHashBtnClick.bind(this));
        this.viewHashBtn.injectAfter(this.input);
        varLSdefault.addHelpInfo(this.viewHashBtn,'LSformElement_password','viewHash');
      }
      
      // Mail
      if (this.params['mail']) {
        if ((this.params.mail['canEdit']==1)||(!$type(this.params.mail['canEdit']))) {
          this.editMailBtn = new Element('img');
          this.editMailBtn.src = varLSdefault.imagePath('mail-edit.png');
          this.editMailBtn.addClass('btn');
          this.editMailBtn.addEvent('click',this.onEditMailBtnClick.bind(this));
          this.LSmail_open = 0;
          this.editMailBtn.injectAfter(this.input);
          varLSdefault.addHelpInfo(this.editMailBtn,'LSformElement_password','editmail');
        }
        if (this.params.mail['ask']) {
          this.mailBtn = new Element('img');
          this.mailBtn.addClass('btn');
          this.mailBtn.addEvent('click',this.onMailBtnClick.bind(this));
          this.mailInput = new Element('input');
          this.mailInput.setProperties({
            name: 'LSformElement_password_' + this.name + '_send',
            type: 'hidden'
          });
          if (this.params.mail['send']) {
            this.mailInput.value = 1;
            this.mailBtn.src = varLSdefault.imagePath('mail.png');
            varLSdefault.addHelpInfo(this.mailBtn,'LSformElement_password','mail');
          }  
          else {
            this.mailInput.value = 0;
            this.mailBtn.src = varLSdefault.imagePath('nomail.png');
            varLSdefault.addHelpInfo(this.mailBtn,'LSformElement_password','nomail');
          }
          this.mailBtn.injectAfter(this.input);
          this.mailInput.injectAfter(this.mailBtn);
        }
      }
      
      // ViewBtn
      this.viewBtn = new Element('img');
      this.viewBtn.src = varLSdefault.imagePath('view.png');
      this.viewBtn.addClass('btn');
      this.viewBtn.addEvent('click',this.changeInputType.bind(this));
      this.viewBtn.injectAfter(this.input);
      varLSdefault.addHelpInfo(this.viewBtn,'LSformElement_password','view');
      
      // Verify
      if (this.params['verify']) {
        this.bgColor = this.input.getStyle('background-color');
        this.verifyFx = new Fx.Tween(this.input,{property: 'background-color',duration:600});
        this.verifyBtn = new Element('img');
        this.verifyBtn.src = varLSdefault.imagePath('verify.png');
        this.verifyBtn.addClass('btn');
        this.verifyBtn.addEvent('click',this.onVerifyBtnClick.bind(this));
        this.verifyBtn.injectAfter(this.input);
        varLSdefault.addHelpInfo(this.verifyBtn,'LSformElement_password','verify');
      }
      
      if (this.params['generate']) {
        this.generateBtn = new Element('img');
        this.generateBtn.src = varLSdefault.imagePath('generate.png');
        this.generateBtn.addClass('btn');
        this.generateBtn.addEvent('click',this.onGenerateBtnClick.bind(this));
        this.generateBtn.injectAfter(this.input);
        varLSdefault.addHelpInfo(this.generateBtn,'LSformElement_password','generate');
      }
      
      this.initialize_input();
    },
    
    initialize_input: function() {
      // Verify
      if (this.params['verify']) {
        this.verifyFx = new Fx.Tween(this.input,{property: 'background-color',duration:600});
      }
    },
    
    onMailBtnClick: function() {
      if (this.mailInput.value==0) {
        this.mailInput.value = 1;
        this.mailBtn.src = varLSdefault.imagePath('mail.png');
        varLSdefault.setHelpInfo(this.mailBtn,'LSformElement_password','mail');
      }
      else {
        this.mailInput.value = 0;
        this.mailBtn.src = varLSdefault.imagePath('nomail.png');
        varLSdefault.setHelpInfo(this.mailBtn,'LSformElement_password','nomail');
      }
    },
    
    onEditMailBtnClick: function(btn) {
      if(!$type(this.LSmail)) {
        this.LSmail = new LSmail();
        this.LSmail.addEvent('close',this.onLSmailClose.bind(this));
        this.LSmail.addEvent('valid',this.onLSmailValid.bind(this));
      }
      
      var mail = varLSform.getValue(this.params.mail['mail_attr']);

      this.LSmail_open = 1;
      this.LSmail.setMails(mail);
      this.LSmail.setSubject(this.params.mail['subject']);
      this.LSmail.setMsg(this.params.mail['msg']);
      this.LSmail.open(this.editMailBtn);
    },
    
    onLSmailClose: function(LSmail) {
      LSdebug('LSformElement_password : close LSmail');
      this.LSmail_open = 0;
    },
    
    onLSmailValid: function(LSmail) {
      LSdebug('LSformElement_password : valid LSmail');
      this.setMail(LSmail.getMail());
    },
    
    setMail: function(mail) {
      if ($type(mail)) {
        if (!$type(this.msgInput)) {
          this.msgInput = new Element('input');
          this.msgInput.setProperties({
            name: 'LSformElement_password_' + this.name + '_msg',
            type: 'hidden'
          });
          this.msgInput.injectAfter(this.editMailBtn);
        }
        this.msgInput.value = JSON.encode(mail);
      }
    },
    
    onGenerateBtnClick: function() {
      var data = {
        template:   'LSformElement_password',
        action:     'generatePassword',
        attribute:  this.name,
        objecttype: varLSform.objecttype,
        objectdn:   varLSform.objectdn,
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
        varLSdefault.setHelpInfo(this.viewBtn,'LSformElement_password','hide');
      }
      else {
        var newType = 'password';
        this.viewBtn.src=varLSdefault.imagePath('view.png');
        varLSdefault.setHelpInfo(this.viewBtn,'LSformElement_password','view');
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
        template:   'LSformElement_password',
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
    },

    onViewHashBtnClick: function() {
      var data = {
        template:   'LSformElement_password',
        action:     'viewHash',
        attribute:  this.name,
        objecttype: varLSform.objecttype,
        objectdn:   varLSform.objectdn
      };
      data.imgload=varLSdefault.loadingImgDisplay(this.viewHashBtn);
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onViewHashBtnClickComplete.bind(this)}).send();
    },
    
    onViewHashBtnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        if (data.hash) {
          // ok
          this.input.value=data.hash;
          this.changeInputType('view');
        }
      }
    }
});
