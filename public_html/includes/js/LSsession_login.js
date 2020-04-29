var LSsession_login = new Class({
    initialize: function(){
      this.select_ldapserver = $('LSsession_ldapserver');
      if ( ! this.select_ldapserver )
        return;
      this.loading_zone = $('loading_zone');
      this.recoverPasswordElements = $$('.LSsession_recoverPassword');
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
        noLSsession:  1,
        template:     'LSsession',
        action:       'onLdapServerChangedLogin',
        server:       server,
        imgload:      imgload
      };
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onLdapServerChangedComplete.bind(this)}).send();
    },

    onLdapServerChangedComplete: function(responseText, responseXML){
      varLSdefault.loadingImgHide();
      var data = JSON.decode(responseText);
      LSdebug(data);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        if (data.list_topDn) {
          $('LSsession_topDn').getParent().set('html',data.list_topDn);
          LSdebug($('LSsession_topDn').innerHTML);
          $('LSsession_topDn_label').set('html',data.subDnLabel);
          $$('.loginform-level').each(function(el) {
            el.setStyle('display','block');
          });
        }
        else {
          this.loginformLevelHide();
        }
        if (data.recoverPassword) {
          this.recoverPasswordElements.each(function(el) {
            el.removeClass('LSsession_recoverPassword_hidden');
          },this);
        }
        else {
          this.recoverPasswordElements.each(function(el) {
            el.addClass('LSsession_recoverPassword_hidden');
          },this);
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
