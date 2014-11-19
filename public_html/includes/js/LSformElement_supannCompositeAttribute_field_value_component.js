var LSformElement_supannCompositeAttribute_field_value_component = new Class({
    initialize: function(p,name,field_name){
      this.p = p;
      this.field_name = field_name;
      this.name = name;
      this.params = varLSdefault.LSjsConfig[this.field_name];
      
      this.input = p.getElement('input');
      this.img = p.getElement('img');
      this.span = p.getElement('span');
      
      if (this.params.components[this.name].type=="table" || this.params.components[this.name].type=="codeEntite") {
		  this.inputSearch=new Element(
			'input',
			{
				'class': 'LSformElement_supannCompositeAttribute_search',
				'styles': {
					'display': 'none'
				}
			}
		  );
		  this.inputSearch.addEvent('keydown',this.onKeyUpInputSearch.bindWithEvent(this));
		  this.inputSearch.injectInside(this.p);
		  
		  this.searchBtn=new Element(
			'img',
			{
				'src': varLSdefault.imagePath('modify'),
				'alt': this.params.searchBtn,
				'title': this.params.searchBtn,
			}
		  );
		  this.searchBtn.addEvent('click',this.toogleInputSearch.bind(this));
		  this.searchBtn.injectBefore(this.inputSearch);
		  
		  this._lastSearch=null;
		  this._possibleValues=null;
	  }
    },
    
    toogleInputSearch: function() {
		if (this.inputSearch.getStyle('display')=='none') {
			this.inputSearch.setStyle('display','block');
			this.inputSearch.focus();
		}
		else {
			this.hidePossibleValues();
			this.inputSearch.setStyle('display','none');
			this.inputSearch.set('value','');
		}
	},
    
    onKeyUpInputSearch: function(event) {
      event = new Event(event);
      
      if ((event.key=='enter')||(event.key=='tab')) {
        event.stop();
        if (this.inputSearch.value!="") {
          this.launchSearch();
        }
      }
      
      if (event.key=='esc') {
        this.toogleInputSearch();
      }
    },
    
    launchSearch: function() {
		this.hidePossibleValues();
		this._lastSearch=this.inputSearch.value;
		var data = {
		  template:   'LSformElement_supannCompositeAttribute',
		  action:     'searchComponentPossibleValues',
		  attribute:  this.field_name,
		  objecttype: varLSform.objecttype,
		  idform:     varLSform.idform,
		  component:  this.name,
		  pattern:    this.inputSearch.value
		};
		data.imgload=varLSdefault.loadingImgDisplay(this.inputSearch);
		new Request({url: 'index_ajax.php', data: data, onSuccess: this.onSearchComplete.bind(this)}).send();
    },
    
    onSearchComplete: function(responseText, responseXML) {
		var data = JSON.decode(responseText);
		if ( varLSdefault.checkAjaxReturn(data) ) {
		  this.displayPossibleValues(data.possibleValues);
		}
    },
    
    displayPossibleValues: function(possibleValues) {
		if (this._possibleValues==null) {
			this._possibleValues=new Element(
				'div',
				{
						'class': 'supannCompositeAttribute_possibleValues',
				}
			);
			this._possibleValues.injectInside(this.p);
		}
		

		var ul=new Element('ul');
		possibleValues.each(function(v) {
			var li=new Element(
				'li',
				{
						'data-value': v.value,
						'data-label': v.label,
						'data-translated': v.translated,
				}
			);
			if (v.label!='no') {
					li.set('html',"<img src='"+varLSdefault.imagePath('supann_label_'+v.label)+"' alt='["+v.label+"]'/> "+v.translated);
			}
			else {
				li.set('html',v.translated);
			}
			li.injectInside(this);
		}, ul);
		if (ul.getElements('li').length==0) {
			new Element(
				'li',
				{
						'html': this.params.noResultLabel
				}
			).injectInside(ul);
		}
		else {
			ul.getElements('li').each(function(li) {
				li.addEvent('click',this.onClickPossibleValue.bindWithEvent(this));
			}, this);
		}
		ul.injectInside(this._possibleValues);
		this._possibleValues.setStyle('display', 'block');
	},
	
	hidePossibleValues: function() {
		if (this._possibleValues!=null) {
			this._possibleValues.setStyle('display', 'none');
			this._possibleValues.empty();
		}
	},
	
	onClickPossibleValue: function(event) {
		this.hidePossibleValues();
		event = new Event(event);
		var li=$(event.target);
		if (event.target.tagName=='IMG') {
			li=li.getParent();
		}
		this.input.set('value',li.get('data-value'));
		if (li.get('data-label')!='no') {
			if (this.img==null) {
				this.img=new Element('img');
				this.img.injectBefore(this.span);
			}
			this.img.set('src',varLSdefault.imagePath('supann_label_'+li.get('data-label')));
		}
		this.span.set('html',li.get('data-translated'));
		this.toogleInputSearch();
	},
	
	clear: function() {
		if (this.img) {
			this.img.dispose();
			this.img=null;
		}
		this.input.set('value','');
		if (this.span) {
			this.span.set('html',this.params.noValueLabel);
		}
	}
});
