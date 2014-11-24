var LSformElement_supannLabeledValue_field_value = new Class({
    initialize: function(li,name,field_type){
		this.li=li;
		this.name = name;
		this.field_type = field_type;
        this.params = varLSdefault.LSjsConfig[this.name];

        this.input = this.li.getElement('input');
        this.img = this.li.getElement('img.LSformElement_supannLabeledValue_label');
        this.span = this.li.getElement('span');
        
		this.inputSearch=new Element(
		'input',
		{
			'class': 'LSformElement_supannLabeledValue_search',
			'styles': {
				'display': 'none'
			}
		}
		);
		this.inputSearch.addEvent('keydown',this.onKeyUpInputSearch.bindWithEvent(this));
		this.inputSearch.injectInside(this.li);

		this.searchBtn=new Element(
			'img',
			{
				'src': varLSdefault.imagePath('modify'),
				'alt': this.params.searchBtn,
				'title': this.params.searchBtn,
			}
		);
		this.searchBtn.addEvent('click',this.toogleInputSearch.bind(this));
		this.searchBtn.injectAfter(this.span);

		this._lastSearch=null;
		this._possibleValues=null;
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
		  template:   'LSformElement_supannLabeledValue',
		  action:     'searchPossibleValues',
		  attribute:  this.name,
		  objecttype: varLSform.objecttype,
		  idform:     varLSform.idform,
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
						'class': 'supannLabeledValue_possibleValues',
				}
			);
			this._possibleValues.injectInside(this.li);
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
				this.img=new Element('img',{'class': 'LSformElement_supannLabeledValue_label'});
				this.img.injectBefore(this.span);
			}
			this.img.set('src',varLSdefault.imagePath('supann_label_'+li.get('data-label')));
		}
		this.span.set('html',li.get('data-translated'));
		this.span.set('title',li.get('data-value'));
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
	},

});
