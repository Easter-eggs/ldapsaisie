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
      $$('td.LSobject-list-names').each(function(el) {
        el.addEvent('mouseenter',this.onTdLSobjectListNamesOver.bind(this,el));
      }, this);
      $$('td.LSobject-list-names').each(function(el) {
        el.addEvent('mouseleave',this.onTdLSobjectListNamesOut.bind(this,el));
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
      }, this);
    },

    onTdLSobjectListNamesClick: function(td) {
      window.location=td.getFirst().href;
    },

    onTdLSobjectListNamesOver: function(td){
      td.imgEdit = new Element('img');
      td.imgEdit.src = varLSdefault.imagePath('view.png');
      td.imgEdit.injectInside(td);
    },
    
    onTdLSobjectListNamesOut: function(td) {
      if(td.imgEdit)
        td.imgEdit.destroy();
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
    }

});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSview = new LSview();
});