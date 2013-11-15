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
      // Class of UL
      if (this.params.multiple) {
        this.ul.addClass('LSformElement_select_object_edit');
      }
      
      // Delete btns
      this.ul.getElements('a.LSformElement_select_object').each(function(a){
        this.addDeleteBtn(a);
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
        this.addBtn.addEvent('click',this.onAddBtnClick.bindWithEvent(this));
        this.addBtn.injectInside(this.li);
        varLSdefault.addHelpInfo(this.addBtn,'LSformElement_select_object','add');
        
        this.li.inject(this.ul,'top');
      }
      else {
        this.addSingleAddBtn(this.ul.getFirst());
      }
      
      this._searchAddOpen = 0;
      document.addEvent('click',this.closeIfOpenSearchAdd.bind(this));
      this.addSearchAddBtn();
    },
    
    addDeleteBtn: function(a) {
      var btn = new Element('img');
      btn.addClass('btn');
      btn.setProperties({
        src:    varLSdefault.imagePath('delete'),
        alt:    this.params.deleteBtns
      });
      btn.addEvent('click',this.onDeleteBtnClick.bind(this,btn));
      btn.injectAfter(a);
      varLSdefault.addHelpInfo(btn,'LSformElement_select_object','delete');
    },
    
    addSingleAddBtn: function(insideEl) {
      this.addBtn = new Element('img');
      this.addBtn.setProperty('src',varLSdefault.imagePath('modify'));
      this.addBtn.addClass('btn');
      this.addBtn.addEvent('click',this.onAddBtnClick.bindWithEvent(this));
      this.addBtn.injectInside(insideEl);
      varLSdefault.addHelpInfo(this.addBtn,'LSformElement_select_object','add');
    },
    
    addSearchAddBtn: function() {
      this.searchAddBtn = new Element('img');
      this.searchAddBtn.setProperty('src',varLSdefault.imagePath('add'));
      this.searchAddBtn.addClass('btn');
      this.searchAddBtn.addEvent('click',this.onSearchAddBtnClick.bindWithEvent(this));
      this.searchAddBtn.injectAfter(this.addBtn);
      varLSdefault.addHelpInfo(this.searchAddBtn,'LSformElement_select_object','searchAdd');
    },
    
    onAddBtnClick: function(event) {
      new Event(event).stop();
      
      values = new Array();
      var inputname=this.name+'[]';
      this.ul.getElements('input.LSformElement_select_object').each(function(el) {
        if (el.name==inputname) {
          values.push(el.getProperty('value'));
        }
      }, this);
      
      var data = {
        template:   'LSselect',
        action:     'refreshSession',
        objecttype: this.params['object_type'],
        values:     JSON.encode(values)
      };
      
      data.imgload=varLSdefault.loadingImgDisplay(this.addBtn,'inside');
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onAddBtnClickComplete.bind(this)}).send();
    },
    
    onAddBtnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        varLSsmoothbox.asNew();
        varLSsmoothbox.addEvent('valid',this.onLSsmoothboxValid.bind(this));
        varLSsmoothbox.displayValidBtn();
        varLSsmoothbox.openURL('select.php?LSobject='+this.params['object_type']+((this.params['multiple'])?'&multiple=1':'')+((this.params['filter64'])?'&filter64='+this.params['filter64']:''),{width: 635});
      }
    },
    
    onLSsmoothboxValid: function() {
      var data = {
        template:   'LSformElement_select_object',
        action:     'refresh',
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
        this.clearUl();
        if ($type(data.objects)) {
          var objs = new Hash(data.objects);
          objs.each(this.addLi,this);
        }
        this.addNoValueLabelIfEmpty();
      }
    },
    
    clearUl: function() {
      if (this.params.multiple) {
        this.ul.getElements('li.LSformElement_select_object').each(function(li){
          li.destroy();
        });
      }
      else {
        var a = this.ul.getElement('a.LSformElement_select_object');
        if ($type(a)) {
          a.set('html',this.params.noValueLabel);
          a.removeClass('LSformElement_select_object_deleted');
          var input = this.ul.getElement('input.LSformElement_select_object');
          input.value = "";
        }
      }
    },
    
    clearUlIfNoValue: function() {
      if (!$type(this.ul.getElement('a.LSformElement_select_object'))) {
        this.clearUl();
      }
    },
    
    addLi: function(name,dn) {
      if (this.params.multiple) { // Multiple
        var current = 0;
        this.ul.getElements("input[type=hidden]").each(function(input){
          if ((input.value==dn)&&(input.name != this.name+'[]')) {
            current=input;
          }
        },this);
        if (current) {
          this.toggleDeleteLi(current.getParent());
          return true;
        }
        var li = new Element('li');
        li.addClass('LSformElement_select_object');
        
        var a = new Element('a');
        a.addClass('LSformElement_select_object');
        a.href="view.php?LSobject="+this.params['object_type']+"&dn="+dn;
        a.set('html',name);
        a.injectInside(li);
        
        var input = new Element('input');
        input.setProperties({
          type:   'hidden',
          value:  dn,
          name:   this.name+'[]'
        });
        input.addClass('LSformElement_select_object');
        input.injectAfter(a);
        
        this.addDeleteBtn(a);
        
        li.injectInside(this.ul);
      }
      else { // Non Multiple
        var a = this.ul.getElement('a');
        if ($type(a)) { // Deja initialise
          a.href="view.php?LSobject="+this.params['object_type']+"&dn="+dn;
          a.set('html',name);
          a.removeClass('LSformElement_select_object_deleted');
        
          var input = this.ul.getElement('input');
          input.setProperties({
            value:  dn,
            name:   this.name+'[]'
          });
        }
        else { // Non initialise (No Value)
          this.ul.empty();
          var li = new Element('li');
          
          var a = new Element('a');
          a.addClass('LSformElement_select_object');
          a.href="view.php?LSobject="+this.params['object_type']+"&dn="+dn;
          a.set('html',name);
          a.injectInside(li);
          
          var input = new Element('input');
          input.setProperties({
            type:   'hidden',
            value:  dn,
            name:   this.name+'[]'
          });
          input.addClass('LSformElement_select_object');
          input.injectAfter(a);
          
          this.addDeleteBtn(a);
          li.injectInside(this.ul);
          this.addSingleAddBtn(li);
          this.addSearchAddBtn();
        }
      }
    },
    
    addNoValueLabelIfEmpty: function() {
      if (this.params.multiple) {
        if (!$type(this.ul.getElement('a.LSformElement_select_object'))) {
          var li = new Element('li');
          li.addClass('LSformElement_select_object');
          li.addClass('LSformElement_select_object_noValue');
          li.set('html',this.params.noValueLabel);
          li.injectInside(this.ul);
        }
      }
      else {
        var a = this.ul.getElement('a.LSformElement_select_object');
        if ($type(a)) {
          if (a.hasClass("LSformElement_select_object_deleted")) {
            a.set('html',this.params.noValueLabel);
            a.removeClass('LSformElement_select_object_deleted');
            var input = this.ul.getElement('input.LSformElement_select_object');
            input.value = "";
          }
        }
      }
    },
    
    onDeleteBtnClick: function(img) {
      var li = img.getParent();
      this.toggleDeleteLi(li);
    },
    
    toggleDeleteLi: function(li) {
      var a = li.getFirst('a');
      var input = li.getFirst('input');
      if (input.value!="") {
        if (a.hasClass('LSformElement_select_object_deleted')) {
          input.name=this.name+'[]';
          a.removeClass('LSformElement_select_object_deleted');
        }
        else {
          input.name=($random(1,10000));
          a.addClass('LSformElement_select_object_deleted');
        }
      }
    },
    
    onSearchAddBtnClick: function(event) {
      if (this._searchAddOpen==0) {
        this._searchAddOpen = 1;
        if (!$type(this.searchAddInput)) {
          this.tr = this.ul.getParent().getParent();
          
          this.td2 = new Element('td');
          this.td2.addClass('LSformElement_select_object_searchAdd');
          this.td2.injectInside(this.tr);
          
          this.searchAddInput = new Element('input');
          this.searchAddInput.addClass('LSformElement_select_object_searchAdd');
          this.searchAddInput.addEvent('keydown',this.onKeyUpSearchAddInput.bindWithEvent(this));
          this.searchAddInput.injectInside(this.td2);
        }
        else {
          this.searchAddInput.value = "";
        }

        this._lastSearch = "";
        this.searchAddInput.setStyle('display','inline');
        this.searchAddInput.focus();
      }
    },
    
    onKeyUpSearchAddInput: function(event) {
      event = new Event(event);
      
      if ((event.key=='enter')||(event.key=='tab')) {
        event.stop();
        if (this.searchAddInput.value!="") {
          this.launchSearchAdd();
        }
      }
      
      if (event.key=='esc') {
        this.closeSearchAdd();
      }
    },
    
    launchSearchAdd: function() {
      if (this._lastSearch!=this.searchAddInput.value) {
        this._lastSearch=this.searchAddInput.value;
        var data = {
          template:   'LSformElement_select_object',
          action:     'searchAdd',
          attribute:  this.name,
          objecttype: varLSform.objecttype,
          idform:     varLSform.idform,
          pattern:    this.searchAddInput.value
        };
        data.imgload=varLSdefault.loadingImgDisplay(this.searchAddInput);
        new Request({url: 'index_ajax.php', data: data, onSuccess: this.onSearchAddComplete.bind(this)}).send();
      }
    },
    
    onSearchAddComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        if (!$type(this.searchAddUl)) {
          this.searchAddUl = new Element('ul');
          this.searchAddUl.addClass('LSformElement_select_object_searchAdd');
          this.searchAddUl.injectAfter(this.searchAddInput);
        }
        this.searchAddUl.empty();
        if (data.objects) {
          var objs = new Hash(data.objects);
          objs.each(this.addSearchAddLi,this);
        }
        this.addSearchAddNoValueLabelIfEmpty();
        
        this.searchAddUl.setStyle('display','block');
      }
    },
    
    addSearchAddLi: function(name,dn) {
      var current = 0;
      this.ul.getElements("input[type=hidden]").each(function(input){
        if ((input.value==dn)&&(input.name == this.name+'[]')) {
          current=1;
        }
      },this);
      
      var li = new Element('li');
      li.addClass('LSformElement_select_object_searchAdd');
      li.id = dn;
      li.set('html',name);
      li.addEvent('mouseenter',this.onSearchAddLiMouseEnter.bind(this,li));
      li.addEvent('mouseleave',this.onSearchAddLiMouseLeave.bind(this,li));
      if (current) {
        li.addClass('LSformElement_select_object_searchAdd_current');
      }
      else {
        li.addEvent('click',this.onSearchAddLiClick.bind(this,li));
      }
      li.injectInside(this.searchAddUl);
    },
    
    addSearchAddNoValueLabelIfEmpty: function() {
      if (!$type(this.searchAddUl.getElement('li.LSformElement_select_object_searchAdd'))) {
        var li = new Element('li');
        li.addClass('LSformElement_select_object_searchAdd');
        li.set('html',this.params.noResultLabel);
        li.injectInside(this.searchAddUl);
      }
    },
    
    onSearchAddLiMouseEnter: function(li) {
      li.addClass('LSformElement_select_object_searchAdd_over');
    },
    
    onSearchAddLiMouseLeave: function(li) {
      li.removeClass('LSformElement_select_object_searchAdd_over');
    },
    
    onSearchAddLiClick: function(li) {
      this.clearUlIfNoValue();
      this.addLi(li.innerHTML,li.id);
    },
    
    closeIfOpenSearchAdd: function(event) {
      event = new Event(event);
      if (this._searchAddOpen == 1 && event.target!=this.searchAddBtn && event.target!=this.searchAddInput && event.target!=this.searchAddUl) {
        this.closeSearchAdd();
      }
    },
    
    closeSearchAdd: function() {
      this.searchAddInput.setStyle('display','none');
      if ($type(this.searchAddUl)) {
        this.searchAddUl.setStyle('display','none');
      }
      this._searchAddOpen = 0;
    }
});
