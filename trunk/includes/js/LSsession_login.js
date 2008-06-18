var LSsession_login = new Class({
    initialize: function(){
      this.select_ldapserver = $('LSsession_ldapserver');
      if ( ! this.select_ldapserver ) 
        return;
      this.loading_zone = $('loading_zone');
      this.select_ldapserver.addEvent('change',this.onLdapServerChanged.bind(this));
      this.onLdapServerChanged();
    },

    disableInput: function() {
      $$('input').each(function(el) {
        el.setProperty('disabled','1');
      });
    },

    enableInput: function() {
      $$('input').each(function(el) {
        el.setProperty('disabled','');
      });
    },

    onLdapServerChanged: function(){
      this.disableInput();
      var imgload = varLSdefault.loadingImgDisplay(this.loading_zone,'inside','big');
      var server = this.select_ldapserver.value;
      var data = {
        template: 'login',
        action:   'onLdapServerChanged',
        server:   server,
        imgload:  imgload
      };
      new Ajax('index_ajax.php',  {data: data, onComplete: this.onLdapServerChangedComplete.bind(this)}).request();
    },

    onLdapServerChangedComplete: function(responseText, responseXML){
      varLSdefault.loadingImgHide();
      var data = Json.evaluate(responseText);
      LSdebug(data);
      if ( data ) {
        if (data.LSdebug) {
          varLSdefault.displayDebug(data.LSdebug);
        }
        if (data.LSerror) {
          varLSdefault.displayError(data.LSerror);
          this.loginformLevelHide();
          return;
        }
        if (data.list_topDn) {
          $('LSsession_topDn').getParent().setHTML(data.list_topDn);
          LSdebug($('LSsession_topDn').innerHTML);
          $('LSsession_topDn_label').setHTML(data.levelLabel);
          $$('.loginform-level').each(function(el) {
            el.setStyle('display','block');
          });
        }
        else {
          this.loginformLevelHide();
        }
      }
      else {
        this.loginformLevelHide();
      }
      this.enableInput();
    },
    
    loginformLevelHide: function(){
      $$('.loginform-level').each(function(el) {
        el.setStyle('display','none');
      });
      $('LSsession_topDn').empty();
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSsession_login = new LSsession_login();
});
