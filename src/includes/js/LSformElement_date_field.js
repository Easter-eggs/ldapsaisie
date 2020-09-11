var LSformElement_date_field = new Class({
    initialize: function(name,input){
      this.name = name;
      this.input = input;
      this.specialValueInputs = $$(input.getAllNext('input.LSformElement_date[type=radio]'));

      this.input.addEvent('change', this.onInputChange.bind(this));
      this.specialValueInputs.each(function(input) {
        input.addEvent('click', this.onSpecialValueInputClick.bind(this));
      }, this);

      this.params = varLSdefault.LSjsConfig[this.name];
      if (!$type(this.params)) {
        this.params={};
      }
      if (!$type(this.params.time)) {
        this.params.time = true;
      }
      if (!$type(this.params.manual)) {
        this.params.manual = true;
      }
      if (!$type(this.params.showNowButton)) {
        this.params.showNowButton = true;
      }
      if (!$type(this.params.showTodayButton)) {
        this.params.showNowButton = true;
      }
      if (!$type(this.params.style)) {
        this.params.style = 'dashboard';
      }

      if (!$type(this.params.format)) {
        if (this.params.time) {
          this.params.format = "%d/%m/%Y, %H:%M:%S";
        }
        else {
          this.params.format = "%d/%m/%Y";
        }
      }
      Date.defineParser(this.params.format);

      this.calendar = new DatePicker(this.input, {
          format: this.params.format,
          timePicker: this.params.time,
          pickerClass: 'datepicker_'+this.params.style,
          blockKeydown: (!this.params.manual),
          useFadeInOut: !Browser.ie
        }
      );
      this.calendar.addEvent('onSelect', this.onInputChange.bind(this));

      if (this.params.showNowButton) {
        this.nowBtn = new Element('img');
        this.nowBtn.src = varLSdefault.imagePath('now');
        this.nowBtn.addClass('btn');
        this.nowBtn.addEvent('click',this.onNowBtnClick.bind(this));
        this.nowBtn.injectAfter(this.input);
        varLSdefault.addHelpInfo(this.nowBtn,'LSformElement_date','now');
      }

      if (this.params.showTodayButton) {
        this.todayBtn = new Element('img');
        this.todayBtn.src = varLSdefault.imagePath('calendar');
        this.todayBtn.addClass('btn');
        this.todayBtn.addEvent('click',this.onTodayBtnClick.bind(this));
        if (!$type(this.nowBtn)) {
          this.todayBtn.injectAfter(this.input);
        }
        else {
          this.todayBtn.injectAfter(this.nowBtn);
        }
        varLSdefault.addHelpInfo(this.todayBtn,'LSformElement_date','today');
      }
    },

    onInputChange: function() {
      if (!this.input.value)
        return true;
      this.specialValueInputs.each(function(input) {
        input.removeProperty('checked');
      }, this);
    },

    onSpecialValueInputClick: function() {
      this.input.value="";
    },

    onNowBtnClick: function() {
      this.input.value = new Date().format(this.params.format);
      this.input.fireEvent('change');
    },

    onTodayBtnClick: function() {
      if (this.input.value && this.params.time) {
        // Date & time already defined: just change date and leave same time

        // Parse current value
        var cur = Date.parse(this.input.value,this.params.format);
        if (cur == null) {
          // On fail, try to parse value without specify format
          cur = Date.parse(this.input.value);
        }

        if (cur) {
          // Current value parsed, clone it and change date
          var now = new Date();
          var today = cur.clone();
          today.set({
            year: now.get('year'),
            mo: now.get('mo'),
            date: now.get('date')
          });
          this.input.value = today.format(this.params.format);
          this.input.fireEvent('change');
          return true;
        }
        else
          LSdebug("onTodayBtnClick(): fail to parse current input value => use current date");
      }
      this.input.value = new Date().format(this.params.format);
      this.input.fireEvent('change');
      return true;
    },

    clearValue: function() {
      this.input.value="";
      this.specialValueInputs.each(function(input) {
        input.removeProperty('checked');
      }, this);
    }
});
