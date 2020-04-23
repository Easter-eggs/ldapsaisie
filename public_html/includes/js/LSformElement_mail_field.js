var LSformElement_mail_field = new Class({
    initialize: function(name, input){
      this.name = name;
      this.input = input;
      this.ul = input.getParent('ul');
      this.li = input.getParent('li');
      this.keyUpTimer = null;
      this.lastKeyUpValue = null;
      this.lastAutocompletePattern = null;
      this.lastAutocompleteMails = null;
      this.initialiseLSformElement_mail_field();
    },

    initialiseLSformElement_mail_field: function() {
      this.input.addEvent('keyup',this.onKeyUp.bindWithEvent(this));
      this.input.addEvent('keydown',this.onKeyDown.bindWithEvent(this));
    },

    onKeyDown: function(event) {
      event = new Event(event);
      if (event.key=='tab' && this.input.value) {
        event.stop();
        if (this.keyUpTimer) {
          clearTimeout(this.keyUpTimer);
        }
        this.launchAutocomplete(this.input.value);
      }
    },

    onKeyUp: function(event) {
      this.lastKeyUpValue = this.input.value;
      if (this.keyUpTimer) {
        clearTimeout(this.keyUpTimer);
      }
      if (this.lastKeyUpValue) {
        this.keyUpTimer = this.onkeyUpTimeout.delay(800, this);
      }
    },

    onkeyUpTimeout: function() {
      this.keyUpTimer = null;
      if (this.lastKeyUpValue == this.input.value) {
        this.launchAutocomplete(this.input.value);
      }
    },

    launchAutocomplete: function(pattern) {
      if (this.lastAutocompletePattern == pattern) {
        if (!this.autocompleteIsOpen()) this.showAutocompleteMails();
        return true;
      }
      this.input.set('disabled', 'disabled');
      this.lastAutocompletePattern=pattern;
      var data = {
        template:   'LSformElement_mail',
        action:     'autocomplete',
        attribute:  this.name,
        objecttype: varLSform.objecttype,
        idform:     varLSform.idform,
        pattern:    pattern
      };
      data.imgload=varLSdefault.loadingImgDisplay(this.input);
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onAutocompleteComplete.bind(this)}).send();
    },

    onAutocompleteComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      this.input.erase('disabled');
      if ( varLSdefault.checkAjaxReturn(data) ) {
        this.lastAutocompleteMails = new Hash(data.mails);
        this.showAutocompleteMails();
      }
    },

    showAutocompleteMails: function() {
      if (!this.lastAutocompleteMails) return;
      if (!$type(this.autocompleteUl)) {
        this.autocompleteUl = new Element('ul');
        this.autocompleteUl.addClass('LSformElement_mail_autocomplete');
        this.autocompleteUl.injectInside(this.li);
        document.addEvent('click', this.closeAutocompleteIfOpen.bind(this));
      }
      this.autocompleteUl.empty();
      if (this.lastAutocompleteMails) {
        this.lastAutocompleteMails.each(this.addAutocompleteLi, this);
      }
      this.addAutocompleteNoValueLabelIfEmpty();

      this.autocompleteUl.setStyle('display','block');
    },

    addAutocompleteLi: function(name, mail) {
      var current = 0;
      this.ul.getElements("input").each(function(input){
        if (input.value==mail && input != this.input) {
          current=1;
        }
      },this);

      var li = new Element('li');
      li.addClass('LSformElement_mail_autocomplete');
      li.set('data-mail', mail);
      li.set('html', name);
      li.addEvent('mouseenter',this.onAutocompleteLiMouseEnter.bind(this,li));
      li.addEvent('mouseleave',this.onAutocompleteLiMouseLeave.bind(this,li));
      if (current) {
        li.addClass('LSformElement_mail_autocomplete_current');
      }
      else {
        li.addEvent('click',this.onAutocompleteLiClick.bind(this,li));
      }
      li.injectInside(this.autocompleteUl);
    },

    addAutocompleteNoValueLabelIfEmpty: function() {
      if (this.autocompleteUl.getElement('li') == null) {
        var li = new Element('li');
        li.addClass('LSformElement_mail_autocomplete');
        li.set('html', varLSdefault.LSjsConfig['LSformElement_mail_autocomplete_noResultLabel']);
        li.injectInside(this.autocompleteUl);
      }
    },

    onAutocompleteLiMouseEnter: function(li) {
      li.addClass('LSformElement_mail_autocomplete_over');
    },

    onAutocompleteLiMouseLeave: function(li) {
      li.removeClass('LSformElement_mail_autocomplete_over');
    },

    onAutocompleteLiClick: function(li) {
      this.closeAutocomplete();
      if (li.get('data-mail')) {
        this.input.value = li.get('data-mail');
      }
    },

    autocompleteIsOpen: function() {
      return ($type(this.autocompleteUl) == 'element' && this.autocompleteUl.getStyle('display') != 'none');
    },

    closeAutocomplete: function() {
      if (!this.autocompleteIsOpen()) return true;
      this.autocompleteUl.setStyle('display', 'none');
    },

    closeAutocompleteIfOpen: function(event) {
      event = new Event(event);
      if (!this.autocompleteIsOpen())
        return true;
      if (event.target==this.input || event.target==this.autocompleteUl)
        return true;
      this.closeAutocomplete();
    },

});
