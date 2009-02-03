var LSformElement_date_field = new Class({
    initialize: function(name,input){
      this.name = name;
      this.input = input;
      this.calendarBtn = new Element('img');
      this.calendarBtn.src = varLSdefault.imagePath('calendar.png');
      this.calendarBtn.addClass('btn');
      this.calendarBtn.addEvent('click',this.onCalendarBtnClick.bind(this));
      this.calendarBtn.injectAfter(this.input);
      varLSdefault.addHelpInfo(this.calendarBtn,'LSformElement_date','calendar');
      
      // Récupération des paramètres à partir de l'attribut 'rem' du bouton
      this.params = varLSdefault.LSjsConfig[this.name];
      if (!$type(this.params)) {
        this.params={};
      }
      if (!$type(this.params.format)) {
        this.params.format = "%d/%m/%Y, %H:%M:%S";
      }
      if (!$type(this.params.firstDayOfWeek)) {
        this.params.firstDayOfWeek=0;
      }
      
      this.input.addEvent('click',this.onCalendarBtnClick.bind(this));
      
      this.date = Date.parseDate(this.input.value,this.params.format);
      
      this.calendar = new Calendar(
        this.params.firstDayOfWeek,
        this.date,
        this.onChangeCalendar.bind(this),
        this.onCloseCalendar.bind(this)
      );
      this.calendar.setDateFormat(this.params.format);
      this.calendar.showsTime = true;
      this.calendar.create();
      
      this.nowBtn = new Element('img');
      this.nowBtn.src = varLSdefault.imagePath('now.png');
      this.nowBtn.addClass('btn');
      this.nowBtn.addEvent('click',this.onNowBtnClick.bind(this));
      this.nowBtn.injectAfter(this.calendarBtn);
      varLSdefault.addHelpInfo(this.nowBtn,'LSformElement_date','now');
    },
    
    onCalendarBtnClick: function() {
      this.calendar.showAtElement(this.calendarBtn);
    },
    
    onChangeCalendar: function(calendar, date) {
      this.input.value = date;
    },
    
    onCloseCalendar: function() {
      this.calendar.hide();
    },
    
    onNowBtnClick: function() {
      this.input.value = new Date().print(this.params.format);
    },
});
