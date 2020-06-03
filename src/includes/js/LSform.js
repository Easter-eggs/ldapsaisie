var LSform = new Class({
    initialize: function(){
      this._modules=[];
      this._fields=[];
      this._elements=[];
      this.listeners = {
        init:    new Array(),
        submit:  new Array()
      };

      if ($type($('LSform_idform'))) {
        this.objecttype = $('LSform_objecttype').value;
        this.objectdn = $('LSform_objectdn').value;
        this.idform = $('LSform_idform').value;
      }

      this.initializeLSform();
      this.initializeLSformLayout();
    },

    initializeLSform: function(el) {
      this.params={};
      if (this.idform) {
        if (typeof(el) == 'undefined') {
          el = document;
        }
        el.getElements('ul.LSform').each(function(ul) {
          this._elements[ul.id] = new LSformElement(this,ul.id,ul);
        }, this);
        this.params=varLSdefault.LSjsConfig['LSform_'+this.idform];
        if (!$type(this.params)) {
          this.params={};
        }
        this._ajaxSubmit=this.params.ajaxSubmit;

        this.warnBox = new LSinfosBox({
          name: 'LSformWarnBox',
          fxDuration: 600,
          closeBtn: 1,
          autoClose: 0
        });

        if ($type(this.params.warnings)) {
          this.warnTxt = '<ul>';
          this.params.warnings.each(function(w) {
            this.warnTxt +='<li>'+w+'</li>';
          },this);
          this.warnTxt += '</ul>';
          this.warnBox.display(this.warnTxt);
        }
        LSdebug(this.params);

        this.listAvailableDataEntryForm=$('LSform_listAvailableDataEntryForm');
        if ($type(this.listAvailableDataEntryForm)) {
          this.listAvailableDataEntryForm.addEvent('change',this.onListAvailableDataEntryFormChange.bind(this));
        }
      }

      LSforms = $$('form.LSform');
      if ($type(LSforms[0])) {
        this.LSform = LSforms[0];
        this.LSform.addEvent('submit',this.ajaxSubmit.bindWithEvent(this));
      }

      this.fireEvent.bind(this)('init');
    },

    initializeLSformLayout: function(el) {
      $$('.LSform_layout').each(function(el) {
        el.addClass('LSform_layout_active');
      },this);

      var LIs = $$('li.LSform_layout');
      LIs.each(function(li) {
        var Layout = this.getLayout(li);
        if ($type(Layout)) {
          if ($type(Layout.getElement('dt.LSform-errors'))) {
            LSdebug('add');
            li.addClass('LSform_layout_errors');
          }
          else {
            if (!$type(Layout.getElement('dt'))) {
              li.destroy();
              Layout.destroy();
            }
          }
        }

        li.getFirst('a').addEvent('click',this.onTabBtnClick.bindWithEvent(this,li));
      },this);

      if (LIs.length != 0) {
        var defaut_on_first = true;
        this._currentTab = 'default_value';
        if (window.location.hash) {
          var li = $('LSform_layout_btn_'+window.location.hash.substr(1));
          if (li) {
            defaut_on_first = false;
            li.getFirst('a').fireEvent('click');
            // Scroll on top of the page
            window.scroll(0, 0);
          }
        }
        if (defaut_on_first) {
          document.getElement('li.LSform_layout').getFirst('a').fireEvent('click');
        }
      }

      var checkUrl = new RegExp('^object/[^/]+/(create|[^/]+(%3D|=)[^/]+(/modify)?)$');
      document.getElements('a.LSview-actions').each(function(a) {
        if (checkUrl.exec(a.get('href'))) {
          a.addEvent('click',this.onActionBtnClick.bindWithEvent(this,a));
        }
      },this);
    },

    onActionBtnClick: function(event,a) {
      if (!location.hash) {
        return true;
      }
      if ($type(event)) {
        event = new Event(event);
        event.stop();
      }
      var href=a.href;
      var checkExistingHash=new RegExp('^([^#]+)#[^#]+$');
      var cur = checkExistingHash.exec(href);
      if (cur) {
        href=cur[1]+location.hash;
      }
      else {
        href=href+location.hash;
      }
      window.location=href;
    },

    getLayoutBtn: function(div) {
      var getName = new RegExp('LSform_layout_div_(.*)');
      var name = getName.exec(div.id);
      if (!name) {
        return;
      }
      return $('LSform_layout_btn_'+name[1]);
    },

    getLayoutNameFromBtn: function(btn) {
      var getName = new RegExp('LSform_layout_btn_(.*)');
      var name = getName.exec(btn.id);
      if (!name) {
        return;
      }
      return name[1];
    },

    getLayout: function(btn) {
      var name = this.getLayoutNameFromBtn(btn);
      if (!name) {
        return;
      }
      return $('LSform_layout_div_'+name);
    },

    onTabBtnClick: function(event,li) {
      if ($type(event)) {
        event = new Event(event);
        event.stop();
        if ($type(event.target.blur)) {
          event.target.blur();
        }
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
        var a=li.getElement('a');
        if ($type(a)) {
          location.hash='#'+this.getLayoutNameFromBtn(li);
        }
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
          LSdebug('No reinitialize for ' + fieldType);
        }
      }
    },

    addField: function(name,obj) {
      this._fields[name]=obj;
    },

    clearFieldValue: function(name) {
      if ($type(this._fields[name]) && $type(this._fields[name].clearValue)) {
        this._fields[name].clearValue();
      }
    },

    getValue: function(fieldName) {
      var retVal = Array();
      var inputs = this.getInput(fieldName);
      inputs.each(function(el){
        if (el.value!="") {
          retVal.include(el.value);
        }
      },this);
      return retVal;
    },

    getInput: function(fieldName) {
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
              retVal.include(el);
            }
          }
        },this);
      }
      return retVal;
    },

    ajaxSubmit: function(event) {
      this.checkUploadFileDefined();

      if (this._ajaxSubmit) {
        event = new Event(event);
        event.stop();

        this.fireEvent.bind(this)('submit');

        this.LSformAjaxInput = new Element('input');
        this.LSformAjaxInput.setProperties ({
          type:   'hidden',
          name:   'ajax',
          value:  '1'
        });
        this.LSformAjaxInput.injectInside(this.LSform);

        this.LSform.set('send',{
          data:         this.LSform,
          onSuccess:    this.onAjaxSubmitComplete.bind(this),
          url:          this.LSform.get('action'),
          imgload:      varLSdefault.loadingImgDisplay($('LSform_title'),'inside')
        });
        this.LSform.send();
      }
      else {
        if($type(this.LSformAjaxInput)) {
          this.LSformAjaxInput.dispose();
        }
      }
    },

    checkUploadFileDefined: function() {
      this.LSform.getElements('input[type=file]').each(function(ipt) {
        if (ipt.files.length!=0) {
          this._ajaxSubmit=0;
        }
      }, this);
    },

    onAjaxSubmitComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        this.resetErrors();
        if ($type(data.LSformErrors) == 'object') {
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

        var dt = ul.getParent('dd.LSform').getPrevious('dt');
        if ($type(dt)) {
          dt.addClass('LSform-errors');
        }

        var layout = ul.getParent('div.LSform_layout_active');
        if ($type(layout)) {
          var li = this.getLayoutBtn(layout);
          if($type(li)) {
            li.addClass('LSform_layout_errors');
          }
        }
      }
      else {
        this.tmp=name+" :</br><ul>";
        errors = new Array(errors);
        errors.each(function(error){
          this.tmp += "<li>"+error+"</li>";
        },this);
        this.tmp +="</ul>";
        this.warnBox.display(this.tmp);
      }
    },

    onListAvailableDataEntryFormChange: function() {
      var url=window.location.pathname+"?LSobject="+this.objecttype
      if (this.listAvailableDataEntryForm.value!="") {
        url+="&LSform_dataEntryForm="+this.listAvailableDataEntryForm.value;
      }
      document.location=url;
    },

    addEvent: function(event,fnct) {
      if ($type(this.listeners[event])) {
        if ($type(fnct)=="function") {
          this.listeners[event].include(fnct);
        }
      }
    },

    fireEvent: function(event) {
      LSdebug('LSform :: fireEvent('+event+')');
      if ($type(this.listeners[event])) {
        this.listeners[event].each(function(fnct) {
          try {
            fnct(this);
          }
          catch(e) {
            LSdebug('LSform :: '+event+'() -> rater');
          }
        },this);
      }
    }

});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSform = new LSform();
});
