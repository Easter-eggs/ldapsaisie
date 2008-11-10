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
      
      LSforms = $$('form.LSform');
      if ($type(LSforms[0])) {
        this.LSform = LSforms[0];
        this.LSformAjaxInput = new Element('input');
        this.LSformAjaxInput.setProperties ({
          type:   'hidden',
          name:   'ajax',
          value:  '1'
        });
        this.LSformAjaxInput.injectInside(this.LSform);
        
        this.LSform.addEvent('submit',this.ajaxSubmit.bindWithEvent(this));
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

    getLayoutBtn: function(div) {
      var getName = new RegExp('LSform_layout_div_(.*)');
      var name = getName.exec(div.id);
      if (!name) {
        return;
      }
      return $('LSform_layout_btn_'+name[1]);
    },
    
    getLayout: function(btn) {
      var getName = new RegExp('LSform_layout_btn_(.*)');
      var name = getName.exec(btn.id);
      if (!name) {
        return;
      }
      return $('LSform_layout_div_'+name[1]);
    },
    
    onTabBtnClick: function(event,li) {
      if ($type(event)) {
        event = new Event(event);
        event.stop();
        event.target.blur();
      }
      
      if (this._currentTab!=li) {
        if (this._currentTab!='default_value') {
          this._currentTab.removeClass('LSform_layout_current');
          var oldDiv = this.getLayout(this._currentTab);
          if ($type(oldDiv)) {
            oldDiv.removeClass('LSform_layout_current');
          }
        }
        
        this._currentTab = li;
        li.addClass('LSform_layout_current');
        var div = this.getLayout(li);
        if ($type(div)) {
          div.addClass('LSform_layout_current');
          
          // Focus
          var ul = div.getElement('ul.LSform');
          if ($type(ul)) {
            var el = ul.getElement('input');
            if (!$type(el)) {
              el = ul.getElement('textarea');
            }
            if (!$type(el)) {
              el = ul.getElement('select');
            }
            if ($type(el)) {
              if(el.type!='hidden') {
                el.focus();
              }
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
    },
    
    ajaxSubmit: function(event) {
      event = new Event(event);
      event.stop();
      
      this.resetErrors();
      
      this.LSform.set('send',{
        data:         this.LSform,
        onSuccess:    this.onAjaxSubmitComplete.bind(this),
        url:          this.LSform.get('action'),
        imgload:      varLSdefault.loadingImgDisplay($('LSform_title'),'inside')
      });
      this.LSform.send();
    },
    
    onAjaxSubmitComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        if ($type(data.LSformRedirect)) {
          if (!$type(data.LSdebug)) {
            (function(addr){document.location = addr;}).delay(1000,this,data.LSformRedirect);
          }
        }
        else if ($type(data.LSformErrors) == 'object') {
          data.LSformErrors = new Hash(data.LSformErrors);
          data.LSformErrors.each(this.addError,this);
        }
      }
    },
    
    resetErrors: function() {
      $$('dd.LSform-errors').each(function(dd) {
        dd.destroy();
      });
      $$('dt.LSform-errors').each(function(dt) {
        dt.removeClass('LSform-errors');
      });
      $$('li.LSform_layout_errors').each(function(li) {
        li.removeClass('LSform_layout_errors');
      });
      
    },
    
    addError: function(errors,name) {
      var ul = $(name);
      if ($type(ul)) {
        errors = new Array(errors);
        errors.each(function(txt){
          var dd = new Element('dd');
          dd.addClass('LSform');
          dd.addClass('LSform-errors');
          dd.set('html',txt);
          dd.injectAfter(this.getParent());
        },ul);
        
        var dt = ul.getParent().getPrevious('dt');
        dt.addClass('LSform-errors');
        
        var layout = ul.getParent('div.LSform_layout_active');
        if ($type(layout)) {
          var li = getLayoutBtn(layout);
          if($type(li)) {
            li.addClass('LSform_layout_errors');
          }
        }
      }
    },
    
    addTip: function(el) {
      this.LStips.attach(el);
    },
    
    removeTip: function(el) {
      this.LStips.detach(el);
    }
    
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSform = new LSform();
});
