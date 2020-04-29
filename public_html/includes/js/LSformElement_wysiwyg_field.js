var LSformElement_wysiwyg_field = new Class({
    initialize: function(name,textarea){
      this.name = name;
      this.textarea = textarea;
      this.params = varLSdefault.getParams(this.name);
      this.initialiseLSformElement_wysiwyg_field();
    },

    initialiseLSformElement_wysiwyg_field: function() {
      var options = {};
      if ($type(this.params.extra_options) == 'object') {
        options = this.params.extra_options;
      }
      options.target = this.textarea;
      options.language = varLSdefault.getCurrentLang();
      tinymce.init(options);
    },

});
