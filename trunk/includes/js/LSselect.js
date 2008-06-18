var LSselect = new Class({
    initialize: function(){
      this.main_page = $('LSobject-select-main-div').getParent();
      this.content = $('content');
      
      this.LSselect_search_form = $('LSselect_search_form');
      var input = new Element('input');
      input.setProperty('name','ajax');
      input.setProperty('type','hidden');
      input.injectInside(this.LSselect_search_form);
      
      this.LSselect_search_form.addEvent('submit',this.onSubmitSearchForm.bindWithEvent(this));
      
      this.LSselect_topDn = $('LSselect_topDn');
      if (this.LSselect_topDn) {
        this.LSselect_topDn.addEvent('change',this.onChangeLSselect_topDn.bind(this));
      }
      this.LSselect_refresh_btn = $('LSselect_refresh_btn');
      this.LSselect_refresh_btn.addEvent('click',this.onClickLSselect_refresh_btn.bind(this));
      
      this.initializeContent();
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
      }
      else {
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
      varLSdefault.loadingImgHide(data.imgload);
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
      this.content.setHTML(responseText);
      this.initializeContent();
    },
    
    onChangeLSselect_topDn: function() {
      this.submitSearchForm();
    },
    
    onSubmitSearchForm: function(event) {
      new Event(event).stop();
      this.submitSearchForm();
    },
    
    submitSearchForm: function() {
      var imgload = varLSdefault.loadingImgDisplay($('title'),'inside');
      this.LSselect_search_form.send({
        update: this.content,
        onComplete: this.onSubmitSearchFormComplete.bind(this,imgload),
        evalScripts: true
      });
    },
    
    onSubmitSearchFormComplete: function(imgload) {
      varLSdefault.loadingImgHide(imgload);
      if (typeof(debug_txt)!="undefined") {
        var debug = Json.evaluate(debug_txt);
        if (debug) {
          varLSdefault.displayDebug(debug.toString());
        }
      }
      if (typeof(error_txt)!="undefined") {
        var error=Json.evaluate(error_txt);
        if (error) {
          varLSdefault.displayDebug(error.toString());
        }
      }
      this.initializeContent();
    },

    onClickLSselect_refresh_btn: function() {
      var input = new Element('input');
      input.setProperty('name','refresh');
      input.setProperty('type','hidden');
      input.injectInside(this.LSselect_search_form);
      this.submitSearchForm();
    }
});
