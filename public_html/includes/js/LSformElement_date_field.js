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

      this.calendar = new DatePicker(this.input, {
          format: this.params.format,
          timePicker: this.params.time,
          pickerClass: 'datepicker_'+this.params.style,
          blockKeydown: (!this.params.manual),
          useFadeInOut: !Browser.ie
        }
      );

      this.nowBtn = new Element('img');
      this.nowBtn.src = varLSdefault.imagePath('now.png');
      this.nowBtn.addClass('btn');
      this.nowBtn.addEvent('click',this.onNowBtnClick.bind(this));
      this.nowBtn.injectAfter(this.input);
      varLSdefault.addHelpInfo(this.nowBtn,'LSformElement_date','now');
    },
    
    onNowBtnClick: function() {
      this.input.value = new Date().format(this.params.format);
    }
});
