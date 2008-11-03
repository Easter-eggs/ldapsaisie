var LSformElement_select_object_field = new Class({
    initialize: function(ul){
      this.ul=ul;
      this.dd=ul.getParent();
      this.name = ul.id;
      this.params = varLSdefault.LSjsConfig[this.name];
      if ($type(this.params)) {
        this.initializeLSformElement_select_object();
      }
    },
    
    initializeLSformElement_select_object: function() {
      // Class du UL
      if (this.params.multiple) {
        this.ul.addClass('LSformElement_select_object_edit');
      }
      
      // Delete btns
      this.ul.getElements('a.LSformElement_select_object').each(function(a){
        var btn = new Element('img');
        btn.addClass('btn');
        btn.setProperties({
          src:    varLSdefault.imagePath('delete.png'),
          alt:    this.params.deleteBtns
        });
        btn.addEvent('click',this.LSformElement_select_object_deleteBtn.bind(this,btn));
        btn.injectAfter(a);
      },this);
      
      if (this.params.multiple) {
        // li
        this.ul.getElements('li').each(function(li){
          li.addClass('LSformElement_select_object');
        },this);
        
        // Head
        this.li = new Element('li');
        this.li.addClass('LSformElement_select_object_addBtn');
        
        this.addBtn = new Element('span');
        this.addBtn.addClass('btn');
        this.addBtn.set('html',this.params.addBtn);
        this.addBtn.addEvent('click',this.onLSformElement_select_object_addBtnClick.bindWithEvent(this));
        this.addBtn.injectInside(this.li);
        
        this.li.inject(this.ul,'top');
      }
      else {
        this.addSingleAddBtn(this.ul.getFirst());
      }
    },
    
    addSingleAddBtn: function(insideEl) {
      this.addBtn = new Element('img');
      this.addBtn.setProperty('src',varLSdefault.imagePath('modify.png'));
      this.addBtn.addClass('btn');
      this.addBtn.addEvent('click',this.onLSformElement_select_object_addBtnClick.bindWithEvent(this));
      this.addBtn.injectInside(insideEl);
    },
    
    reinitialize: function() {
      this.ul = this.dd.getFirst('ul');
      this.initializeLSformElement_select_object();
    },
    
    onLSformElement_select_object_addBtnClick: function(event) {
      new Event(event).stop();
      
      values = new Array();
      this.ul.getElements('input.LSformElement_select_object').each(function(el) {
        values.push(el.getProperty('value'));
      }, this);
      
      var data = {
        template:   'LSselect',
        action:     'refreshSession',
        objecttype: this.params['object_type'],
        values:     JSON.encode(values)
      };
      
      data.imgload=varLSdefault.loadingImgDisplay(this.addBtn,'inside');
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onLSformElement_select_object_addBtnClickComplete.bind(this)}).send();
    },
    
    onLSformElement_select_object_addBtnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        varLSsmoothbox.asNew();
        varLSsmoothbox.addEvent('valid',this.onLSsmoothboxValid.bind(this));
        varLSsmoothbox.displayValidBtn();
        varLSsmoothbox.openURL('select.php?LSobject='+this.params['object_type']+((this.params['multiple'])?'&multiple=1':''),{width: 615});
      }
    },
    
    onLSsmoothboxValid: function() {
      var data = {
        template:   'LSform',
        action:     'refreshField',
        attribute:  this.name,
        objecttype: varLSform.objecttype,
        objectdn:   varLSform.objectdn,
        idform:     varLSform.idform
      };
      data.imgload=varLSdefault.loadingImgDisplay(this.addBtn);
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onLSsmoothboxValidComplete.bind(this)}).send();
    },
    
    onLSsmoothboxValidComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        this.dd.set('html',data.html);
        this.reinitialize();
      }
    },
    
    LSformElement_select_object_deleteBtn: function(img) {
      var li = img.getParent();
      var a = li.getFirst('a');
      var input = li.getFirst('input');
      if (a.hasClass('LSformElement_select_object_deleted')) {
        input.name=this.name+'[]';
        a.addClass('LSformElement_select_object');
        a.removeClass('LSformElement_select_object_deleted');
      }
      else {
        input.name=($random(1,10000));
        a.addClass('LSformElement_select_object_deleted');
        a.removeClass('LSformElement_select_object');
      }
    }
});
