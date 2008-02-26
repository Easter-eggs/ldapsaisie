var LSsession_login = new Class({
    initialize: function(){
      this.select_ldapserver = $('LSsession_ldapserver');
      if ( ! this.select_ldapserver ) 
        return;
      this.select_ldapserver.addEvent('change',this.onLdapServerChanged.bind(this));
    },

    onLdapServerChanged: function(){
      var imgload = varLSdefault.loadingImgDisplay(this.select_ldapserver);
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
        if (data.LSerror) {
          varLSdefault.displayError(data.LSerror);
          return;
        }
        else {
          $('LSsession_topDn').getParent().setHTML(data.list_topDn);
          LSdebug($('LSsession_topDn').innerHTML);
          $$('.loginform-level').each(function(el) {
            el.setStyle('display','block');
          });
        }
      }
      else {
        $$('.loginform-level').each(function(el) {
          el.setStyle('display','none');
        });
        $('LSsession_topDn').empty();
      }
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSsession_login = new LSsession_login();
});
