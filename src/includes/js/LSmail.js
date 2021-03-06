var LSmail = new Class({
    initialize: function(mails,subject,msg){
      this.href = "LSmail.php";
      this.setMails(mails);
      this.setMsg(msg);
      this.setSubject(subject);
      this.object = {};
      this.opened = 0;
      this.options = {
        display_mail_field:     1,
        display_subject_field:  1
      };
      this.listeners = {
        close:    new Array(),
        valid:  new Array()
      };
    },

    setMails: function(mails) {
      if ($type(mails)) {
        this.mails = mails;
      }
      else {
        this.mails = new Array();
      }
    },

    setMsg: function(msg) {
      if ($type(msg)) {
        this.msg = msg;
      }
      else {
        this.msg = "";
      }
    },

    setSubject: function(subject) {
      if ($type(subject)) {
        this.subject = subject;
      }
      else {
        this.subject = "";
      }
    },

    setObject: function(type,dn) {
      this.object = {
        type:   type,
        dn:     dn
      };
    },

    setOption: function(option,value) {
      this.options[option]=value;
    },

    hideMailField: function() {
      this.setOption('display_mail_field',0);
    },

    hideSubjectField: function() {
      this.setOption('display_subject_field',0);
    },

    setClass: function(c) {
      this.setOption('class',c);
    },

    open: function(startElement) {
      if (this.opened==0) {
        var data = {
          object:     this.object,
          mails:      this.mails,
          msg:        this.msg,
          subject:    this.subject,
          options:    this.options
        };

        if ($type(startElement)) {
          this.startElement = startElement;
          data.imgload=varLSdefault.loadingImgDisplay(startElement);
        }

        new Request({url: 'ajax/class/LSmail/display', data: data, onSuccess: this.onOpenGetHtmlComplete.bind(this)}).send();
      }
    },

    onOpenGetHtmlComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        varLSsmoothbox.asNew();
        varLSsmoothbox.addEvent('valid',this.onLSsmoothboxValid.bind(this));
        varLSsmoothbox.addEvent('close',this.onLSsmoothboxClose.bind(this));
        varLSsmoothbox.openHTML(data.html,{startElement: this.startElement, width: 800, height: 400});
      }
    },

    onLSsmoothboxValid: function(LSsmoothbox) {
      if($type(LSsmoothbox.frame)) {
        this.sendInfos = {
          mail:     LSsmoothbox.frame.getElementById('LSmail_mail').value,
          subject:  LSsmoothbox.frame.getElementById('LSmail_subject').value,
          msg:      LSsmoothbox.frame.getElementById('LSmail_msg').value
        };
      }
      this.fireEvent.bind(this)('valid');
    },

    onLSsmoothboxClose: function(LSsmoothbox) {
      this.opened=0;
      this.fireEvent.bind(this)('close');
    },

    send: function() {
      if ($type(this.sendInfos)) {
        var data = {
          infos:      this.sendInfos
        };
        data.imgload=varLSdefault.loadingImgDisplay(this.startElement);
        new Request({url: 'ajax/class/LSmail/send', data: data, onSuccess: this.onSendComplete.bind(this)}).send();
      }
    },

    getMail: function() {
      return this.sendInfos;
    },

    onSendComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        if ($type(data.msgok)) {
          varLSdefault.LSinfos.display(data.msgok);
        }
      }
    },

    addEvent: function(event,fnct) {
      if ($type(this.listeners[event])) {
        if ($type(fnct)=="function") {
          this.listeners[event].include(fnct);
        }
      }
    },

    fireEvent: function(event) {
      LSdebug('LSmail :: fireEvent('+event+')');
      if ($type(this.listeners[event])) {
        this.listeners[event].each(function(fnct) {
          try {
            fnct(this);
          }
          catch(e) {
            LSdebug('LSmail :: '+event+'() -> rater');
          }
        },this);
      }
    }
});
