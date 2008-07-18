var LSformElement_date_field = new Class({
    initialize: function(calendarBtn){
      this.calendarBtn = calendarBtn;
      this.calendarBtn.addEvent('click',this.onCalendarBtnClick.bind(this));
      
      // Récupération des paramètres à partir de l'attribut 'rem' du bouton
      this.params = varLSdefault.LSjsConfig[this.calendarBtn.id];
      if (!$type(this.params)) {
        this.params={};
      }
      if (!$type(this.params.format)) {
        this.params.format = "%d/%m/%Y, %H:%M:%S";
      }
      if (!$type(this.params.firstDayOfWeek)) {
        this.params.firstDayOfWeek=0;
      }
      
      this.input = calendarBtn.getParent().getFirst();
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
    },
    
    onCalendarBtnClick: function() {
      this.calendar.showAtElement(this.calendarBtn);
    },
    
    onChangeCalendar: function(calendar, date) {
      this.input.value = date;
    },
    
    onCloseCalendar: function() {
      this.calendar.hide();
    }
});
