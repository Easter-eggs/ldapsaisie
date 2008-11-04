var LSform = new Class({
    initialize: function(){
      this._modules=[];
      this._elements=[];
      
      if ($type($('LSform_idform'))) {
        this.objecttype = $('LSform_objecttype').value;
        this.objectdn = $('LSform_objectdn').value;
        this.idform = $('LSform_idform').value;
      }
      
      this.initializeLSform();
      this.initializeLSformLayout();
    },
    
    initializeLSform: function(el) {
      this.LStips = new Tips('.LStips');
      if (this.idform) {
        if (typeof(el) == 'undefined') {
          el = document;
        }
        el.getElements('ul.LSform').each(function(ul) {
          this._elements[ul.id] = new LSformElement(this,ul.id,ul);
        }, this);
      }
    },
    
    initializeLSformLayout: function(el) {
      $$('.LSform_layout').each(function(el) {
        el.addClass('LSform_layout_active');
      },this);
      
      var LIs = $$('li.LSform_layout');
      LIs.each(function(li) {
        li.getFirst('a').addEvent('click',this.onTabBtnClick.bindWithEvent(this,li));
      },this);
      
      if (LIs.length != 0) {
        this._currentTab = 'default_value';
        document.getElement('li.LSform_layout').getFirst('a').fireEvent('click');
      }
    },
    
    onTabBtnClick: function(event,li) {
      if ($type(event)) {
        event = new Event(event);
        event.stop();
        event.target.blur();
      }
      
      if (this._currentTab!='default_value') {
        var oldLi = $$('li.LSform_layout[title='+this._currentTab+']');
        if ($type(oldLi)) {
          oldLi.removeClass('LSform_layout_current');
        }
        var oldDiv = $$('div.LSform_layout[title='+this._currentTab+']');
        if ($type(oldDiv)) {
          oldDiv.removeClass('LSform_layout_current');
        }
      }
      
      this._currentTab = li.title;
      li.addClass('LSform_layout_current');
      var div = $$('div.LSform_layout[title='+this._currentTab+']');
      if ($type(div)) {
        div = div[0];
        div.addClass('LSform_layout_current');
        
        // Focus
        var ul = div.getElement('ul.LSform');
        if ($type(ul)) {
          var el = ul.getElement('input');
          if (!$type(el)) {
            el = ul.getElement('textarea');
          }
          if ($type(el)) {
            if(el.type!='hidden') {
              el.focus();
            }
          }
        }
      }
      
    },
    
    addModule: function(name,obj) {
      this._modules[name]=obj;
    },
    
    initializeModule: function(fieldType,li) {
      if ($type(this._modules[fieldType])) {
        try {
          this._modules[fieldType].reinitialize(li);
        }
        catch(e) {
          LSdebug('Pas de reinitialise pour ' + fieldType);
        }
      }
    },
    
    getValue: function(fieldName) {
      var retVal = Array();
      var ul = $(fieldName);
      if ($type(ul)) {
        var elements = ul.getElements('input');
        elements.combine(ul.getElements('textarea'));
        elements.combine(ul.getElements('select'));
        
        var getName = new RegExp('([a-zA-Z0-9]*)(\[.*\])?');
        elements.each(function(el){
          var name = getName.exec(el.name);
          LSdebug(name);
          if (name) {
            if (name[1]==fieldName) {
              if ($type(el.value)) {
                if (el.value!="") {
                  retVal.include(el.value);
                }
              }
            }
          }
        },this);
      }
      return retVal;
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSform = new LSform();
});
