var LSdefault = new Class({
    initialize: function(){
			LSdebug('toto');
			this.LSdebug = $('LSdebug');
			this.LSdebugInfos = $('LSdebug_infos');
			this.LSdebug.setOpacity(0);
			if (this.LSdebugInfos.innerHTML != '') {
				this.displayDebugBox();
			}

			this.LSdebugHidden = $('LSdebug_hidden');
			this.LSdebugHidden.addEvent('click',this.onLSdebugHiddenClick.bind(this));
			this.LSerror = $('LSerror');
			this.LSerror.setOpacity(0);
			if (this.LSerror.innerHTML != '') {
				this.displayLSerror();
			}
    },

		onLSdebugHiddenClick: function(){
			new Fx.Style(this.LSdebug,'opacity',{duration:500}).start(1,0);
		},

		displayDebugBox: function() {
			new Fx.Style(this.LSdebug,'opacity',{duration:500}).start(0,0.8);
		},

		displayError: function(html) {
			this.LSerror.empty();
			this.LSerror.setHTML(html);
			this.displayLSerror();
		},

		displayLSerror: function() {
			new Fx.Style(this.LSerror,'opacity',{duration:500}).start(0,0.8);
			(function(){new Fx.Style(this.LSerror,'opacity',{duration:500}).start(0.8,0);}).delay(5000, this);
		},

		loadingImgDisplay: function(el) {
			this.loading_img = new Element('img');
			this.loading_img.src='templates/images/ajax-loader.gif';
			this.loading_img.injectAfter(el);
		},

		loadingImgHide: function() {
			this.loading_img.remove();
		}

});
window.addEvent(window.ie ? 'load' : 'domready', function() {
	varLSdefault = new LSdefault();
});

LSdebug_active = 1;

function LSdebug() {
    if (LSdebug_active != 1) return;
    if (typeof console == 'undefined') return;
    console.log.apply(this, arguments);
}
