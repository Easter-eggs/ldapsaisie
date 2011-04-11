var LSformElement_postaladdress = new Class({
    initialize: function(){
      this.initialiseLSformElement_postaladdress();
      if (typeof(varLSform) != "undefined") {
        varLSform.addModule("LSformElement_postaladdress",this);
      }
    },
    
    initialiseLSformElement_postaladdress: function(el) {
      if (typeof(el) == 'undefined') {
        el = document;
      }
      el.getElements('p.LSformElement_postaladdress').each(function(p) {
        this.addBtnAfter.bind(this)(p);
      }, this);
    },
   
    getFieldName: function(el) {
      try {
        var name = el.getParent().getParent().id;
        return name;
      }
      catch (err) {
        LSdebug(err);
      }
      return;
    },

    getFieldParams: function(el) {
      var name = this.getFieldName(el);
      if (typeof(varLSdefault.LSjsConfig['LSformElement_postaladdress_'+name]) != "undefined") {
        var params = varLSdefault.LSjsConfig['LSformElement_postaladdress_'+name];
        if (typeof(params)!="undefined") {
          return params;
        }
      }
      return;
    },
 
    addBtnAfter: function(el) {
      var name = this.getFieldName(el);
      if (typeof(varLSdefault.LSjsConfig['LSformElement_postaladdress_'+name]) == "undefined") {
        return;
      }
      var btn = new Element('img');
      btn.setProperties({
        src:    varLSdefault.imagePath('map_go.png'),
        alt:    'View on map'
      });
      btn.addClass('btn');
      btn.setStyle('float','left');
      btn.injectBefore(el);
      btn.addEvent('click',this.onBtnClick.bind(this,el));
      varLSdefault.addHelpInfo(btn,'LSformElement_postaladdress','viewOnMap');
    },
    
    reinitialize: function(el) {
      varLSform.initializeModule('LSformElement_textarea',el);
      this.initialiseLSformElement_postaladdress(el);
    },
    
    onBtnClick: function(el) {
      var address = el.get('html');
      if (typeof(address)!="undefined") {
        var params = this.getFieldParams(el);
        if (params && typeof(params.map_url)!="undefined") {
          href = params.map_url;
        }
        window.open(href,'_blank');
      }
    }
});
window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSformElement_postaladdress = new LSformElement_postaladdress();
});
