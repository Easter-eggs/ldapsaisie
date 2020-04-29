var LSformElement_textarea = new Class({
    initialize: function(){
      this.initialiseLSformElement_textarea();
      if ($type(varLSform)) {
        varLSform.addModule("LSformElement_textarea",this);
      }
    },

    initialiseLSformElement_textarea: function(el) {
      if (!$type(el)) {
        el = document;
      }
      el.getElements('textarea.LSform').each(function(textarea) {
        var btn = new Element('img');
        btn.addClass('btn');
        btn.src = varLSdefault.imagePath('clear');
        btn.addEvent('click',this.onClearBtnClick.bind(this,btn));
        btn.injectAfter(textarea);
        varLSdefault.addHelpInfo(btn,'LSformElement_textarea','clear');
      }, this);
    },

    onClearBtnClick: function(btn) {
      btn.getPrevious('textarea').value='';
    },

    reinitialize: function(el) {
      this.initialiseLSformElement_textarea(el);
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_textarea = new LSformElement_textarea();
});
