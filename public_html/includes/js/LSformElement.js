var LSformElement = new Class({
    initialize: function(LSform,name,ul){
      this.LSform=LSform;
      this.name=name;
      this.ul=ul;
      this.fields=[];
      this.multiple = this.ul.hasClass('LSformElement_multiple');
      this.initializeLSformElement();
    },

    initializeLSformElement: function(li) {
      if (typeof(li) == 'undefined') {
        var elements = this.ul.getChildren('li');
      }
      else {
        var elements = [li];
      }
      elements.each(function(li) {
        var id='LSformElement_field_'+this.name+'_'+$random(1,1000);
        this.fields[id] = new LSformElement_field(this,li,id,this.name);
      }, this);
    },

    onAddFieldBtnClick: function(field){
      var data = {
        template:   'LSform',
        action:     'onAddFieldBtnClick',
        attribute:  this.name,
        objecttype: this.LSform.objecttype,
        objectdn:   this.LSform.objectdn,
        idform:     this.LSform.idform,
        fieldId:    field.id
      };
      LSdebug(data);
      data.imgload = varLSdefault.loadingImgDisplay(field.li,'inside');
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onAddFieldBtnClickComplete.bind(this)}).send();
    },

    onAddFieldBtnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      LSdebug(data);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        var li = new Element('li');
        var field = this.fields[data.fieldId];
        li.set('html',data.html);
        li.injectAfter(field.li);
        this.initializeLSformElement(li);
        this.LSform.initializeModule(data.fieldtype,li);
      }
    },

    onRemoveFieldBtnClick: function(field) {
      if (this.ul.getChildren('li').length == 1) {
        field.clearValue.bind(field)();
      }
      else {
        field.remove.bind(field)();
      }
    }
});
