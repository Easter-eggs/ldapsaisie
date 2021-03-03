var LSformElement_text_field = new Class({
    initialize: function(name,input,parent){
      this._start = false;
      this.name = name;
      this.parent = parent;
      this.input = input;
      this.params = varLSdefault.LSjsConfig[this.name];
      this._auto=1;
      this.onChangeColor = '#f16d6d';
      this.generatedValue = "";

      this.ul = input.getParent('ul');
      this.li = input.getParent('li');
      this.keyUpTimer = null;
      this.lastKeyUpValue = null;
      this.lastAutocompletePattern = null;
      this.lastAutocompleteMails = null;
    },

    start: function() {
      if (this._start) {
        return true;
      }
      if ($type(this.params) && $type(this.params.generate_value_format)) {
        this.format = this.params.generate_value_format;
        this.oldBg = this.input.getStyle('background-color');

        this.fx = new Fx.Tween(this.input,{property: 'background-color',duration:600});

        // GenerateBtn
        this.generateBtn = new Element('img');
        this.generateBtn.addClass('btn');
        this.generateBtn.src=varLSdefault.imagePath('generate');
        this.generateBtn.addEvent('click',this.refreshValue.bind(this,true));
        this.generateBtn.injectAfter(this.input);
        varLSdefault.addHelpInfo(this.generateBtn,'LSformElement_text','generate');

        // Auto
        var force=0;
        if (this.params.autoGenerateOnModify) {
          force = 1;
        }
        this.isCreation = false;
        if (this.input.value=="") {
          this.isCreation = true;
        }

        if (((this.isCreation)&&(this.params.autoGenerateOnCreate))||(force)) {
          this.dependsFields = this.parent.getDependsFields(this.format);
          this.dependsFields.each(function(el) {
            var inputs = varLSform.getInput.bind(this.parent)(el);
            if (inputs.length>0) {
              inputs.each(function(input) {
                input.addEvent('change',this.refreshValue.bind(this));
              },this);
            }
          },this);
        }
      }
      if (this.input.hasClass('LSformElement_text_autocomplete')) {
        this.input.addEvent('keyup',this.onKeyUp.bindWithEvent(this));
        this.input.addEvent('keydown',this.onKeyDown.bindWithEvent(this));
      }
      this._start=true;
    },

    refreshValue: function(force) {
      if (force==true) {
        this._auto=1;
      }
      if (((this._auto)||(force==true))&&((this.generatedValue=="")||(this.generatedValue==this.input.value)||(force==true))) {
        var val=getFData(this.format,varLSform,'getValue');
        if ($type(this.params['withoutAccent'])) {
          if(this.params['withoutAccent']) {
            val = replaceAccents(val);
          }
        }
        if ($type(this.params['replaceSpaces'])) {
          if(this.params['replaceSpaces']) {
            val = replaceSpaces(val,this.params['replaceSpaces']);
          }
        }
        if ($type(this.params['upperCase'])) {
          if(this.params['upperCase']) {
            val = val.toUpperCase();
          }
        }
        if ($type(this.params['lowerCase'])) {
          if(this.params['lowerCase']) {
            val = val.toLowerCase();
          }
        }
        this.input.value = val;
        this.generatedValue = val;
        this.fx.start(this.onChangeColor);
        (function() {this.fx.start(this.oldBg);}).delay(1000,this);
        this.input.fireEvent('change');
      }
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
        if (!this.autocompleteIsOpen()) this.showAutocompleteValues();
        return true;
      }
      this.input.set('disabled', 'disabled');
      this.lastAutocompletePattern=pattern;
      var data = {
        attribute:  this.name,
        objecttype: varLSform.objecttype,
        idform:     varLSform.idform,
        pattern:    pattern
      };
      data.imgload=varLSdefault.loadingImgDisplay(this.input);
      new Request({url: 'ajax/class/LSformElement_text/autocomplete', data: data, onSuccess: this.onAutocompleteComplete.bind(this)}).send();
    },

    onAutocompleteComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      this.input.erase('disabled');
      if ( varLSdefault.checkAjaxReturn(data) ) {
        this.lastAutocompleteValues = new Hash(data.values);
        this.showAutocompleteValues();
      }
    },

    showAutocompleteValues: function() {
      if (!this.lastAutocompleteValues) return;
      if (!$type(this.autocompleteUl)) {
        this.autocompleteUl = new Element('ul');
        this.autocompleteUl.addClass('LSformElement_text_autocomplete');
        this.autocompleteUl.injectInside(this.li);
        document.addEvent('click', this.closeAutocompleteIfOpen.bind(this));
      }
      this.autocompleteUl.empty();
      if (this.lastAutocompleteValues) {
        this.lastAutocompleteValues.each(this.addAutocompleteLi, this);
      }
      this.addAutocompleteNoValueLabelIfEmpty();

      this.autocompleteUl.setStyle('display','block');
    },

    addAutocompleteLi: function(name, value) {
      var current = 0;
      this.ul.getElements("input").each(function(input){
        if (input.value==value && input != this.input) {
          current=1;
        }
      },this);

      var li = new Element('li');
      li.addClass('LSformElement_text_autocomplete');
      li.set('data-value', value);
      li.set('html', name);
      li.addEvent('mouseenter',this.onAutocompleteLiMouseEnter.bind(this,li));
      li.addEvent('mouseleave',this.onAutocompleteLiMouseLeave.bind(this,li));
      if (current) {
        li.addClass('LSformElement_text_autocomplete_current');
      }
      else {
        li.addEvent('click',this.onAutocompleteLiClick.bind(this,li));
      }
      li.injectInside(this.autocompleteUl);
    },

    addAutocompleteNoValueLabelIfEmpty: function() {
      if (this.autocompleteUl.getElement('li') == null) {
        var li = new Element('li');
        li.addClass('LSformElement_text_autocomplete');
        li.set('html', varLSdefault.LSjsConfig['LSformElement_text_autocomplete_noResultLabel']);
        li.injectInside(this.autocompleteUl);
      }
    },

    onAutocompleteLiMouseEnter: function(li) {
      li.addClass('LSformElement_text_autocomplete_over');
    },

    onAutocompleteLiMouseLeave: function(li) {
      li.removeClass('LSformElement_text_autocomplete_over');
    },

    onAutocompleteLiClick: function(li) {
      this.closeAutocomplete();
      if (li.get('data-value')) {
        this.input.value = li.get('data-value');
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
