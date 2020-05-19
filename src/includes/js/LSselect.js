var LSselect = new Class({
    initialize: function(){
      this.content = $('content');

      this.LSselect_search_form = $('LSselect_search_form');
      this.id = this.LSselect_search_form.getElement('input[name=LSselect_id]').value;
      this.multiple = this.LSselect_search_form.getElement('input[name=multiple]').value;

      // Add ajax hidden input
      var input = new Element('input');
      input.setProperty('name','ajax');
      input.setProperty('type','hidden');
      input.injectInside(this.LSselect_search_form);

      this.tempInput = [];
      this.lastCheckboxChanged = null;

      this.LSselect_search_form.addEvent('submit',this.onSubmitSearchForm.bindWithEvent(this));

      this.LSselect_topDn = $('LSselect_topDn');
      if (this.LSselect_topDn) {
        this.LSselect_topDn.addEvent('change',this.onChangeLSselect_topDn.bind(this));
      }
      this.LSselect_refresh_btn = $('LSselect_refresh_btn');
      this.LSselect_refresh_btn.addEvent('click',this.onClickLSselect_refresh_btn.bind(this));

      this.initializeContent();
      varLSdefault.ajaxDisplayDebugAndError();

      this.title = $('LSselect_title');
      this.tabs_ul = $$('ul.LSselect_selectable_object_types')[0];
      if (this.tabs_ul) {
        this.initializeTabs();
      }
    },

    initializeTabs: function(ul) {
      this.tabs_ul.getElements('li').addEvent('click', function (event) {
        console.log(event.target);
        this.LSselect_search_form.getElement('input[name=LSobject]').value = event.target.getProperty('data-object-type');
        this.tabs_ul.getElements('li.current').each(function(li){ console.log(li); li.removeClass('current'); });
        event.target.addClass('current');
        this.submitSearchForm();
      }.bind(this));
    },

    initializeContent: function() {
      $$('input.LSobject-select').each(function(el) {
        el.addEvent('click',this.oncheckboxChange.bind(this,el));
      }, this);

      $$('a.LSobject-list-page').each(function(el) {
        el.addEvent('click',this.onChangePageClick.bindWithEvent(this,el));
      }, this);

      $$('.sortBy_displayName').each(function(el) {
        el.addEvent('click',this.sortBy.bind(this,'displayName'));
      }, this);

      $$('.sortBy_subDn').each(function(el) {
        el.addEvent('click',this.sortBy.bind(this,'subDn'));
      }, this);

      $$('td.LSobject-select-names').each(function(el) {
        el.addEvent('click',this.onNameClick.bind(this,el));
      }, this);
    },

    loadingImgDisplay: function(place, position) {
      if (!place) {
        if (this.title) {
          place = this.title;
        }
        else if (this.tabs_ul) {
          place = this.tabs_ul;
        }
      }
      if (!position) {
        position = 'inside';
      }
      return varLSdefault.loadingImgDisplay(place, position);
    },

    oncheckboxChange: function(checkbox){
      this.lastCheckboxChanged = checkbox;
      var url;
      if (checkbox.checked) {
        url = 'ajax/class/LSselect/addSelectedObject';
      }
      else {
        url = 'ajax/class/LSselect/dropSelectedObject';
      }
      var data = {
        LSselect_id:  this.id,
        object_dn:    checkbox.value,
        object_type:  $('LSselect-object').getProperties('caption').caption,
      };
      data.imgload = this.loadingImgDisplay(checkbox.getParent().getNext(), 'inside');
      new Request({url: url, data: data, onSuccess: this.oncheckboxChangeComplete.bind(this)}).send();
    },

    oncheckboxChangeComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      var success = false;
      if(varLSdefault.checkAjaxReturn(data)) {
        success = data.success;
      }
      if (!success && this.lastCheckboxChanged) {
        this.lastCheckboxChanged.checked = !this.lastCheckboxChanged.checked;
      }
    },

    onChangePageClick: function(event, a) {
      new Event(event).stop();
      var data = {
        ajax:         true
      };
      this.searchImgload = this.loadingImgDisplay();
      new Request({url: a.href, data: data, onSuccess: this.onChangePageClickComplete.bind(this)}).send();
    },

    onChangePageClickComplete: function(responseText, responseXML) {
      varLSdefault.loadingImgHide(this.searchImgload);
      this.content.set('html',responseText);
      this.initializeContent();
    },

    onChangeLSselect_topDn: function() {
      this.submitSearchForm();
    },

    onSubmitSearchForm: function(event) {
      new Event(event).stop();
      this.submitSearchForm();
    },

    submitSearchForm: function(loading_img_place) {
      this.searchImgload = this.loadingImgDisplay();
      this.LSselect_search_form.set('send',{
        data:         this.LSselect_search_form,
        evalScripts:  true,
        onSuccess:    this.onSubmitSearchFormComplete.bind(this),
        url:          this.LSselect_search_form.get('action'),
        multiple:     this.multiple
      });
      this.LSselect_search_form.send();
    },

    onSubmitSearchFormComplete: function(responseText, responseXML) {
      varLSdefault.loadingImgHide(this.searchImgload);

      this.content.set('html',responseText);

      varLSdefault.ajaxDisplayDebugAndError();

      this.tempInput.each(function(el) {
        el.destroy();
      },this);

      this.initializeContent();
    },

    onClickLSselect_refresh_btn: function() {
      this.tempInput['refresh'] = new Element('input');
      this.tempInput['refresh'].setProperty('name','refresh');
      this.tempInput['refresh'].setProperty('type','hidden');
      this.tempInput['refresh'].setProperty('value',1);
      this.tempInput['refresh'].injectInside(this.LSselect_search_form);
      this.submitSearchForm();
    },

    sortBy: function(value) {
      this.tempInput['sortBy'] = new Element('input');
      this.tempInput['sortBy'].setProperty('name','sortBy');
      this.tempInput['sortBy'].setProperty('type','hidden');
      this.tempInput['sortBy'].setProperty('value',value);
      this.tempInput['sortBy'].injectInside(this.LSselect_search_form);
      this.submitSearchForm();
    },

    onNameClick: function(td) {
      var input = td.getParent().getFirst().getFirst();
      input.checked = (!input.checked);
      input.fireEvent('click');
    }
});
