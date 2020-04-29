var LSformElement_maildir_field = new Class({
    initialize: function(name,input){
      this.name = name;
      this.input = input;
      this.params = varLSdefault.getParams(this.name);
      this.initialiseLSformElement_maildir_field();
    },

    initialiseLSformElement_maildir_field: function() {
      if (!$type(varLSform.idform)) {
        return true;
      }
      if ($type(this.params.LSform[varLSform.idform])) {
        this.doBtn = new Element('img');
        this.doBtn.addClass('btn');
        this.doBtn.addEvent('click',this.onDoBtnClick.bind(this));
        this.doInput = new Element('input');
        this.doInput.setProperties({
          name: 'LSformElement_maildir_' + this.name + '_do',
          type: 'hidden'
        });
        if (this.params.LSform[varLSform.idform]) {
          this.doInput.value = 1;
          this.doBtn.src = varLSdefault.imagePath('maildir_do');
          varLSdefault.addHelpInfo(this.doBtn,'LSformElement_maildir','do');
        }
        else {
          this.doInput.value = 0;
          this.doBtn.src = varLSdefault.imagePath('maildir_nodo');
          varLSdefault.addHelpInfo(this.doBtn,'LSformElement_maildir','nodo');
        }
        this.doBtn.injectAfter(this.input);
        this.doInput.injectAfter(this.doBtn);
      }
    },

    onDoBtnClick: function() {
      if (this.doInput.value==0) {
        this.doInput.value = 1;
        this.doBtn.src = varLSdefault.imagePath('maildir_do');
        varLSdefault.setHelpInfo(this.doBtn,'LSformElement_maildir','do');
      }
      else {
        this.doInput.value = 0;
        this.doBtn.src = varLSdefault.imagePath('maildir_nodo');
        varLSdefault.setHelpInfo(this.doBtn,'LSformElement_maildir','nodo');
      }
    }

});
