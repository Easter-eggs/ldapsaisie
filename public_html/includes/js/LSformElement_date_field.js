var LSformElement_date_field = new Class({
    initialize: function(name,input){
      this.name = name;
      this.input = input;

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

    onNowBtnClick: function() {
      this.input.value = new Date().format(this.params.format);
    },

    onTodayBtnClick: function() {
      if (this.input.value) {
        var cur = Date.parse(this.input.value,this.params.format);
        if (cur == null) {
          var cur = Date.parse(this.input.value);
        }
        if (cur) {
          var now = new Date();
          var today = cur.clone();
          today.set({
            year: now.get('year'),
            mo: now.get('mo'),
            date: now.get('date')
          });
          this.input.value = today.format(this.params.format);
        }
      }
    }
});
