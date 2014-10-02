var LSview = new Class({
    initialize: function(){
      this.labels = varLSdefault.LSjsConfig['LSview_labels'];
      if (!$type(this.labels)) {
        this.labels = {
          delete_confirm_text:      "Do you really want to delete",
          delete_confirm_title:     "Caution",
          delete_confirm_validate:  "Delete"
        };
      }
      
      $$('td.LSobject-list-names').each(function(el) {
        el.addEvent('click',this.onTdLSobjectListNamesClick.bind(this,el));
      }, this);
      $$('a.LSobject-list-actions').each(function(el) {
        var checkRemove = /remove\.php.*/;
        if (checkRemove.exec(el.href)) {
          el.addEvent('click',this.onRemoveListBtnClick.bindWithEvent(this,el));
        }
      }, this);
      $$('a.LSview-actions').each(function(el) {
        var checkRemove = /remove\.php.*/;
        if (checkRemove.exec(el.href)) {
          el.addEvent('click',this.onRemoveViewBtnClick.bindWithEvent(this,el));
        }
        else if(el.hasClass('LScustomActions')) {
          el.addEvent('click',this.onCustomActionBtnClick.bindWithEvent(this,el));
        }
      }, this);
      
      this.LSsearchForm = $('LSsearch_form');
      this.LSsearchPredefinedFilter = $('LSview_search_predefinedFilter');
      if($type(this.LSsearchPredefinedFilter) && $type('LSsearch_form')) {
        this.LSsearchPredefinedFilter.addEvent('change',this.onLSsearchPredefinedFilterChange.bind(this));
      }
    },
    
    onLSsearchPredefinedFilterChange: function() {
      if (this.LSsearchForm) {
        this.LSsearchForm.submit();
      }
    },

    onTdLSobjectListNamesClick: function(td) {
      window.location=td.getFirst().href;
    },

    onRemoveListBtnClick: function(event,a) {
      Event(event).stop();
      if (!this._confirmBoxOpen) {
        this._confirmBoxOpen = 1;
        var name = a.getParent().getParent().getFirst('td').getElement('a').innerHTML;
        this.confirmBox = new LSconfirmBox({
          text:         this.labels.delete_confirm_text + ' "'+name+'" ?', 
          startElement: a,
          onConfirm:    this.removeFromA.bind(this,a),
          onClose:      this.onConfirmBoxClose.bind(this)
        });
      }
    },
    
    onRemoveViewBtnClick: function(event,a) {
      Event(event).stop();
      if (!this._confirmBoxOpen) {
        this._confirmBoxOpen = 1;
        var name = $('LSview_title').innerHTML;
        this.confirmBox = new LSconfirmBox({
          text:           this.labels.delete_confirm_text + ' "'+name+'" ?',
          title:          this.labels.delete_confirm_title,
          validate_label: this.labels.delete_confirm_yes_btn,
          startElement:   a,
          onConfirm:      this.removeFromA.bind(this,a),
          onClose:        this.onConfirmBoxClose.bind(this)
        });
      }
    },
    
    onConfirmBoxClose: function() {
      this._confirmBoxOpen = 0;
    },
    
    removeFromA: function(a) {
      document.location = a.href+'&valid';
    },

    onCustomActionBtnClick: function(event,a) {
      if (a.hasClass('LScustomActions_noConfirmation')) {
        return true;
      }
      Event(event).stop();
      if (!this._confirmBoxOpen) {
        this._confirmBoxOpen = 1;
        var getName = new RegExp('customAction=([^&]*)');
        var name = getName.exec(a.href)[1];
        if (name) {
          var title = a.innerHTML;
          if ($type(this.labels['custom_action_'+name+'_confirm_text'])) {
            var text = this.labels['custom_action_'+name+'_confirm_text']
          }
          else {
            var objectname = $('LSview_title').innerHTML;
            var text = getFData('Do you really want to execute custom action %{customAction} on %{objectname} ?',{customAction: name, objectname: objectname });
          }
          this.confirmBox = new LSconfirmBox({
            text:           text,
            title:          title,
            startElement:   a,
            onConfirm:      this.executeCustomActionFromA.bind(this,a),
            onClose:        this.onConfirmBoxClose.bind(this)
          });
        }
      }
    },

    executeCustomActionFromA: function(a) {
      document.location = a.href+'&valid';
    }

});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSview = new LSview();
});
