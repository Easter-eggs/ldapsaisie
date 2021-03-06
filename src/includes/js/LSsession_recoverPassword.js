var LSsession_recoverPassword = new Class({
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
        noLSsession:  1,
        server:       server,
        imgload:      imgload
      };
      new Request({url: 'ajax/class/LSsession/onLdapServerChangedLogin', data: data, onSuccess: this.onLdapServerChangedComplete.bind(this)}).send();
    },

    onLdapServerChangedComplete: function(responseText, responseXML){
      varLSdefault.loadingImgHide();
      var data = JSON.decode(responseText);
      LSdebug(data);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        if (data.list_topDn) {
          $('LSsession_topDn').getParent().set('html',data.list_topDn);
          $('LSsession_topDn_label').set('html',data.subDnLabel);
          $$('.recoverpasswordform-level').each(function(el) {
            el.setStyle('display','block');
          });
        }
        else {
          this.recoverpasswordformLevelHide();
        }
        if (data.recoverPassword) {
          this.enableInput();
        }
      }
    },

    recoverpasswordformLevelHide: function(){
      $$('.recoverpasswordform-level').each(function(el) {
        el.setStyle('display','none');
      });
      $('LSsession_topDn').empty();
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSsession_recoverPassword = new LSsession_recoverPassword();
});
