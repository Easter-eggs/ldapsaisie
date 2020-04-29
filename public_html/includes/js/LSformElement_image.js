var LSformElement_image = new Class({
    initialize: function(){
      $$('div.LSformElement_image').each(function(el) {
        el.addEvent('mouseenter',this.onMouseEnterImage.bind(this));
      }, this);

      $$('div.LSformElement_image').each(function(el) {
        el.addEvent('mouseleave',this.onMouseLeaveImage.bind(this));
      }, this);

      $$('img.LSformElement_image_action_zoom').each(function(el) {
        var getId = /LSformElement_image_action_zoom_(.*)/
        var id = getId.exec(el.id)[1];
        var img = $('LSformElement_image_' + id);
        el.addEvent('click',this.zoomImg.bindWithEvent(this,img));
        varLSdefault.addHelpInfo(el,'LSformElement_date','zoom');
      }, this);

      $$('img.LSformElement_image_action_delete').each(function(el) {
        el.addEvent('click',this.onImageDeleteBtnClick.bind(this,el));
        varLSdefault.addHelpInfo(el,'LSformElement_date','delete');
      }, this);
    },

    zoomImg: function(event, img) {
      new Event(event).stop();
      varLSsmoothbox.hideValidBtn();
      varLSsmoothbox.openImg(img.src,{startElement: img});
    },

    onMouseEnterImage: function() {
      $$('ul.LSformElement_image_actions').each(function(el) {
        el.setStyle('visibility','visible');
      }, this);
    },

    onMouseLeaveImage: function() {
      $$('ul.LSformElement_image_actions').each(function(el) {
        el.setStyle('visibility','hidden');
      }, this);
    },

    onImageDeleteBtnClick: function(img) {
      $$('form.LSform').each(function(el) {
        var input = new Element('input');
        input.type = 'hidden';
        var getInputId = /LSformElement_image_action_delete_(.*)/
        var id = 'LSformElement_image_input_' + getInputId.exec(img.id)[1];
        input.name = $(id).name + '_delete';
        input.value='delete';
        input.injectInside(el);
      },this);

      var main = img.getParent().getParent().getParent();
      var hidder = new Fx.Tween(main,{property: 'opacity',duration:600,onComplete:main.dispose.bind(this)});
      hidder.start(1,0);
      //img.getParent().getParent().getParent().destroy();
    }

});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_image = new LSformElement_image();
});
