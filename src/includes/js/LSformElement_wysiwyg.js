var LSformElement_wysiwyg = new Class({
    initialize: function(){
      this.fields=new Hash();
      this.initialiseLSformElement_wysiwyg();
      if ($type(varLSform)) {
        varLSform.addModule("LSformElement_wysiwyg",this);
        varLSform.addEvent("submit", this.onLSformSubmit.bind(this), 'LSformElement_wysiwyg :: tinyMCE.triggerSave()');
      }
    },

    initialiseLSformElement_wysiwyg: function(el) {
      var getName = /^(.*)\[\]$/

      if (!$type(el)) {
        el = document;
      }
      el.getElements('textarea.LSformElement_wysiwyg').each(function(textarea) {
        var name = getName.exec(textarea.name)[1];
        this.fields[name] = new LSformElement_wysiwyg_field(name,textarea);
      }, this);

      el.getElements('div.LSformElement_wysiwyg').each(function(div) {
        // Hide original div
        div.setStyle('display', 'none');

        // Create and inject iframe
        var iframe = new Element('iframe');
        iframe.addClass('LSformElement_wysiwyg');
        iframe.injectAfter(div);

        // Set iframe content
        var doc = iframe.contentWindow.document;
        doc.open();
        doc.write('<html><body style="padding: 0; margin: 0">'+div.innerHTML+'</body></html>');
        doc.close();

        // Set iframe height
        var body = doc.body, html = doc.documentElement;
        var height = Math.max( body.scrollHeight, body.offsetHeight,
                               html.clientHeight, html.scrollHeight,
                               html.offsetHeight );
        iframe.setStyle('height', height+'px');
      }, this);
    },

    reinitialize: function(el) {
      this.initialiseLSformElement_wysiwyg(el);
    },

    onLSformSubmit: function(form, on_confirm, on_cancel) {
      tinyMCE.triggerSave();
      if ($type(on_confirm) == 'function')
        on_confirm();
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_wysiwyg = new LSformElement_wysiwyg();
});
