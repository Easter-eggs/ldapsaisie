var LSselect = new Class({
    initialize: function(){
      this.initializeContent();
      
      $$('form.LSselect_search').each(function(el) {
        var input = new Element('input');
        input.setProperty('name','ajax');
        input.setProperty('type','hidden');
        input.injectInside(el);
        el.addEvent('submit',this.onSubmitSearchForm.bindWithEvent(this,el));
      }, this);
      
      this.LSselect_topDn = $('LSselect_topDn');
      this.LSselect_topDn.addEvent('change',this.onChangeLSselect_topDn.bind(this));
    },
    
    initializeContent: function() {
      $$('input.LSobject-select').each(function(el) {
        el.addEvent('click',this.oncheckboxChange.bind(this,el));
      }, this);
      
      $$('a.LSobject-list-page').each(function(el) {
        el.addEvent('click',this.onChangePageClick.bindWithEvent(this,el));
      }, this);
    },

    oncheckboxChange: function(checkbox){
      if (checkbox.checked) {
        var data = {
          template:   'LSselect',
          action:     'addLSselectobject-item',
          objectdn:   checkbox.value,
          objecttype: $('LSselect-object').getProperties('caption').caption
        };
        LSdebug('plus');
      }
      else {
        LSdebug('mois');
        var data = {
          template:   'LSselect',
          action:     'dropLSselectobject-item',
          objectdn:   checkbox.value,
          objecttype: $('LSselect-object').getProperties('caption').caption
        };        
      }
      LSdebug(data);
      data.imgload=varLSdefault.loadingImgDisplay(checkbox.getParent().getNext(),'inside');
      new Ajax('index_ajax.php',  {data: data, onComplete: this.oncheckboxChangeComplete.bind(this)}).request();
    },

    oncheckboxChangeComplete: function(responseText, responseXML) {
      var data = Json.evaluate(responseText);
      varLSdefault.loadingImgHide(data);
    },
    
    onChangePageClick: function(event, a) {
      new Event(event).stop();
      var data = {
        ajax:         true
      };
      this.searchImgload = varLSdefault.loadingImgDisplay($('title'),'inside');
      new Ajax(a.href,  {data: data, onComplete: this.onChangePageClickComplete.bind(this)}).request();
    },
    
    onChangePageClickComplete: function(responseText, responseXML) {
      varLSdefault.loadingImgHide(this.searchImgload);
      $('content').setHTML(responseText);
      this.initializeContent();
    },
    
    onChangeLSselect_topDn: function() {
      form = this.LSselect_topDn.getParent().getParent();
      this.submitSearchForm(form);
    },
    
    onSubmitSearchForm: function(event, form) {
      new Event(event).stop();
      this.submitSearchForm(form);
    },
    
    submitSearchForm: function(form) {
      var imgload = varLSdefault.loadingImgDisplay($('title'),'inside');
      form.send({
        update: $('content'),
        onComplete: this.onSubmitSearchFormComplete.bind(this,imgload)
      });
    },
    
    onSubmitSearchFormComplete: function(imgload) {
      varLSdefault.loadingImgHide(imgload);
      this.initializeContent();
    },
    
    submit: function() {
      var values = new Array();
      $('content').getElements('input[name^=LSobjects_selected]').each(function(el) {
        values.push(el.value);
      },this);
      return values;
    }


});
