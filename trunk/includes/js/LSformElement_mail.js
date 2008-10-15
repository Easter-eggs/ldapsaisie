var LSformElement_mail = new Class({
    initialize: function(){
      this.initialiseLSformElement_mail();
      if (typeof(varLSform) != "undefined") {
        varLSform.addModule("LSformElement_mail",this);
      }
      this.LSmail_open = 0;
    },
    
    initialiseLSformElement_mail: function(el) {
      if (typeof(el) == 'undefined') {
        el = document;
      }
      el.getElements('input.LSformElement_mail').each(function(input) {
        this.addBtnAfter.bind(this)(input);
      }, this);
      el.getElements('a.LSformElement_mail').each(function(a) {
        this.addBtnAfter.bind(this)(a);
      }, this);
    },
    
    addBtnAfter: function(el) {
      var btn = new Element('img');
      btn.setProperties({
        src:    varLSdefault.imagePath('mail.png'),
        alt:    'Envoyer un mail',
        title:  'Envoyer un mail'
      });
      btn.addClass('btn');
      btn.injectAfter(el);
      btn.addEvent('click',this.onBtnClick.bind(this,btn));
    },
    
    reinitialize: function(el) {
      varLSform.initializeModule('LSformElement_text',el);
      this.initialiseLSformElement_mail(el);
    },
    
    onBtnClick: function(btn) {
      if (this.LSmail_open==0) {
        var mail = btn.getParent().getFirst().innerHTML;
        if ((typeof(mail)!='string')||(mail=='')) {
           mail = btn.getParent().getFirst().value;
        }
        if(!$type(this.LSmail)) {
          this.LSmail = new LSmail();
          this.LSmail.addEvent('close',this.onLSmailClose.bind(this));
          this.LSmail.addEvent('valid',this.onLSmailValid.bind(this));
        }
        if ((mail!="")) {
          this.LSmail_open = 1;
          this.LSmail.setMails([mail]);
          this.LSmail.setObject($('LSform_objecttype').value,$('LSform_objectdn').value);
          this.LSmail.open(btn);
        }
      }
    },
    
    onLSmailClose: function(LSmail) {
      LSdebug('LSformElement_mail : close LSmail');
      this.LSmail_open = 0;
    },
    
    onLSmailValid: function(LSmail) {
      LSdebug('LSformElement_mail : valid LSmail');
      LSmail.send();
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_mail = new LSformElement_mail();
});
