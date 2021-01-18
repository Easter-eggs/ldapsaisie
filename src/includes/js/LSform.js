var LSform = new Class({
    initialize: function(){
      this._modules=[];
      this._fields=[];
      this._elements=[];
      this.listeners = new Hash({
        init:    new Hash(),
        submit:  new Hash()
      });
      this.listeners_answers = new Hash();

      // On non-ajax submit form, store confirmation status;
      this.submit_confirmed = false;

      if ($type($('LSform_idform'))) {
        this.objecttype = $('LSform_objecttype').value;
        this.objectdn = $('LSform_objectdn').value;
        this.idform = $('LSform_idform').value;
      }

      this.submitting = false;

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
        LSdebug('LSform('+this.idform+'): ajaxSubmit='+this._ajaxSubmit);

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
        this.LSform.addEvent('submit',this.onSubmit.bindWithEvent(this));
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

        // Remove corresponding anchor to avoid scrolling on tab change
        $$('a[name='+this.getLayoutNameFromBtn(li)+']').dispose();

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
          var layout_name = this.getLayoutNameFromBtn(li);
          if (layout_name) {
            location.hash='#'+layout_name;
          }
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

    onSubmit: function(event) {
      if (this.submit_confirmed) {
        // On non-ajax form, leave form submitting if already confirmed
        LSdebug('onSubmit(): form submission already confirmed, do not stop submit event');
        return true;
      }

      // Stop form submitting event
      event = new Event(event);
      event.stop();

      // Check if form is already submitting
      if (this.submitting) {
        // Form is already submitting: stop
        LSdebug('onSubmit(): form already submitting...');
        return;
      }
      this.submitting = true;
      LSdebug(this.LSform);
      this.LSform.addClass('submitting');

      // Fire
      LSdebug('onSubmit(): fire submit event');
      this.fireEvent.bind(this)('submit', this.onSubmitConfirm.bind(this));
    },

    onSubmitConfirm: function (confirmed, event) {
      LSdebug("onSubmitConfirm("+confirmed+")");
      if (!confirmed) {
        this.submitting = false;
        this.LSform.removeClass('submitting');
        return;
      }

      // Check file upload to disable ajax submission in this case
      this.checkUploadFileDefined();

      if (this._ajaxSubmit) {
        LSdebug("onSubmitConfirm(): AJAX submission enabled");
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
        LSdebug("onSubmitConfirm(): AJAX submission disabled");
        if($type(this.LSformAjaxInput)) {
          this.LSformAjaxInput.dispose();
        }
        this.submit_confirmed = true;
        LSdebug("onSubmitConfirm("+confirmed+"): non-AJAX form, submit form again with submit_confirmed flag == TRUE.");
        this.LSform.submit();
      }
    },

    checkUploadFileDefined: function() {
      this.LSform.getElements('input[type=file]').each(function(ipt) {
        if (ipt.files.length!=0) {
          LSdebug("checkUploadFileDefined(): input[type=file] detected, disable AJAX submit.");
          this._ajaxSubmit=0;
        }
      }, this);
    },

    onAjaxSubmitComplete: function(responseText, responseXML) {
      this.submitting = false;
      this.LSform.removeClass('submitting');
      var data = JSON.decode(responseText);
      // Handle common Ajax return checks
      varLSdefault.checkAjaxReturn(data);

      // Handle LSform errors
      this.resetErrors();
      if (data && $type(data.LSformErrors) == 'object') {
        data.LSformErrors = new Hash(data.LSformErrors);
        data.LSformErrors.each(this.addError, this);
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

    addEvent: function(event,fnct,fnct_name) {
      if ($type(this.listeners[event])) {
        if ($type(fnct)=="function") {
          if ($type(fnct_name)!="string") {
            fnct_name = generate_uuid();
          }
          else if (this.listeners[event].has(fnct_name)) {
            fnct_name = fnct_name+"_"+generate_uuid();
          }
          this.listeners[event].set(fnct_name, fnct);
        }
      }
    },

    fireEvent: function(event, callback) {
      LSdebug('LSform :: fireEvent('+event+')');
      if (this.listeners.has(event)) {
        // If no listener configured, considered as confirmed and run callback
        if ($type(callback) == "function" && this.listeners[event].getLength() == 0) {
          callback(true, event);
          return;
        }

        // Reset listeners answers state
        this.listeners_answers[event] = new Hash();

        // Run listeners callback
        this.listeners[event].each(function(fnct, listener_uuid) {
          var result;
          try {
            fnct(
              this,
              function() {
                LSdebug('Listener '+listener_uuid+' confirmed');
                this.eventListenerCallback.bind(this)(event, listener_uuid, true, callback);
              }.bind(this),
              function() {
                LSdebug('Listener '+listener_uuid+' cancel');
                this.eventListenerCallback.bind(this)(event, listener_uuid, false, callback);
              }.bind(this)
            );
          }
          catch(e) {
            LSdebug('LSform :: fireEvent('+event+') :: exception occured running listener '+listener_uuid+' => considered as not-confirmed.');
            LSdebug(e);
            result = false;
          }
        },this);
      }
    },

    eventListenerCallback: function(event, listener_uuid, listener_answer, final_callback) {
      // Check event & listener_uuid
      if (!this.listeners.has(event) || !this.listeners[event].has(listener_uuid))
        return;



      // Set listener answers
      this.listeners_answers[event].set(listener_uuid, listener_answer);
      LSdebug('LSform :: eventListenerCallback('+event+', '+listener_uuid+', '+listener_answer+')');

      // Check all listeners have answered
      if (this.listeners_answers[event].getLength() != this.listeners[event].getLength())
        return;

      // Run final callback
      this.onFinalEventListenerCallback.bind(this)(event, final_callback);
    },

    onFinalEventListenerCallback: function(event, final_callback) {
      LSdebug('LSform :: onFinalEventListenerCallback('+event+')');
      if ($type(final_callback) != "function") {
        LSdebug('LSform :: onFinalEventListenerCallback('+event+') : final_callback is not a function, stop.');
        return;
      }

      // Combine all listeners answers
      var final_result = true;
      this.listeners[event].each(function(fnct, listener_uuid) {
        if (!this.listeners_answers[event].has(listener_uuid) || !this.listeners_answers[event][listener_uuid]) {
          final_result = false;
        }
      }, this);

      // Run final callback
      LSdebug('LSform :: onFinalEventListenerCallback('+event+'): run final_callback with final result = '+final_result);
      final_callback(final_result, event);
    },

});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSform = new LSform();
});
