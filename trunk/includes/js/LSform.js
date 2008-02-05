var LSform = new Class({
    initialize: function(){
			this.objecttype = $('LSform_objecttype').value;
			this.idform = $('LSform_idform').value;

			$$('img.LSform-add-field-btn').each(function(el) {
				el.addEvent('click',this.onAddFieldBtnClick.bind(this,el));
			}, this);

			$$('img.LSform-remove-field-btn').each(function(el) {
				el.addEvent('click',this.onRemoveFieldBtnClick.bind(this,el));
			}, this);
    },

		onAddFieldBtnClick: function(img){
			
			var getAttrName = /LSform_add_field_btn_(.*)_.*/
			var attrName = getAttrName.exec(img.id)[1];
			LSdebug(attrName);

			var data = {
				template: 	'LSform',
				action:			'onAddFieldBtnClick',
				attribute: 	attrName,
				objecttype:	this.objecttype,
				idform:			this.idform,
				img:				img.id
			};
			LSdebug(data);
			varLSdefault.loadingImgDisplay(img);
			new Ajax('index_ajax.php',  {data: data, onComplete: this.onAddFieldBtnClickComplete.bind(this)}).request();
		},

		onAddFieldBtnClickComplete: function(responseText, responseXML) {
			varLSdefault.loadingImgHide();
			var data = Json.evaluate(responseText);
			LSdebug(data);
			if ( data ) {
				if ( typeof(data.LSerror) != "undefined" ) {
        	  varLSdefault.displayError(data.LSerror);
          	return;
        	}	
        	else {	
						var li = new Element('li');
						var img = $(data.img);
						li.setHTML(data.html);
						li.injectAfter(img.getParent());
						li.getElements('img[class=LSform-add-field-btn]').each(function(el) {
							el.addEvent('click',this.onAddFieldBtnClick.bind(this,el));
						}, this);
						li.getElements('img[class=LSform-remove-field-btn]').each(function(el) {
							el.addEvent('click',this.onRemoveFieldBtnClick.bind(this,el));
						}, this);
					}
			}
		},

		onRemoveFieldBtnClick: function(img) {
			if (img.getParent().getParent().getChildren().length == 1) {
				img.getPrevious().getPrevious().value='';
			}
			else {
				img.getParent().remove();
			}
		}

});
window.addEvent(window.ie ? 'load' : 'domready', function() {
	varLSform = new LSform();
});
