var LSformElement_ssh_key = new Class({
    initialize: function(){
      $$('span.LSformElement_ssh_key_short_display').each(function(span) {
        span.addEvent('click',this.onShortDisplayClick.bind(this,span));
        varLSdefault.addHelpInfo(span,'LSformElement_ssh_key','display');
      }, this);
    },
    
    onShortDisplayClick: function(span) {
      var p = span.getParent().getFirst('p.LSformElement_ssh_key_value');
      if (typeof(p)) {
        if (p.getStyle('display')=='none') {
          p.setStyle('display','block');
        }
        else {
          p.setStyle('display',' none');
        }
      }
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_ssh_key = new LSformElement_ssh_key();
});
