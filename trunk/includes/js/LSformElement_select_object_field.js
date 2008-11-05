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
        this.addBtn.addEvent('click',this.onLSformElement_select_object_addBtnClick.bindWithEvent(this));
        this.addBtn.injectInside(this.li);
        
        this.li.inject(this.ul,'top');
      }
      else {
        this.addSingleAddBtn(this.ul.getFirst());
      }
      
      this._searchAddOpen = 0;
      document.addEvent('click',this.closeIfOpenSearchAdd.bind(this));
      this.searchAddBtn = new Element('img');
      this.searchAddBtn.setProperty('src',varLSdefault.imagePath('add.png'));
      this.searchAddBtn.addClass('btn');
      this.searchAddBtn.addEvent('click',this.onSearchAddBtnClick.bind(this));
      this.searchAddBtn.injectAfter(this.addBtn);
    },
    
    addDeleteBtn: function(a) {
      var btn = new Element('img');
      btn.addClass('btn');
      btn.setProperties({
        src:    varLSdefault.imagePath('delete.png'),
        alt:    this.params.deleteBtns
      });
      btn.addEvent('click',this.LSformElement_select_object_deleteBtn.bind(this,btn));
      btn.injectAfter(a);
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
      if($type(this.searchAddInput)) {
        this.searchAddInput.injectInside(this.dd);
          if($type(this.searchAddUl)) {
            this.searchAddUl.injectAfter(this.searchAddInput);
          }
      }
    },
    
    onLSformElement_select_object_addBtnClick: function(event) {
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
    
    clearUlIfNoValue: function() {
      if (!$type(this.ul.getElement('a.LSformElement_select_object'))) {
        this.ul.getElements('li.LSformElement_select_object').each(function(li){
          li.destroy();
        });
      }
    },
    
    addLi: function(name,dn) {
      if (this.params.multiple) {
        this.clearUlIfNoValue();
        
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
      else {
        var a = this.ul.getElement('a');
        a.href="view.php?LSobject="+this.params['object_type']+"&dn="+dn;
        a.set('html',name);
        
        var input = this.ul.getElement('input');
        input.setProperties({
          value:  dn,
          name:   this.name+'[]'
        });
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
    },
    
    onSearchAddBtnClick: function() {
      if (this._searchAddOpen==0) {
        this._searchAddOpen = 1;
        if (!$type(this.searchAddInput)) {
          this.searchAddInput = new Element('input');
          this.searchAddInput.addClass('LSformElement_select_object_searchAdd');
          this.searchAddInput.addEvent('keydown',this.onKeyUpSearchAddInput.bindWithEvent(this));
          this.searchAddInput.injectAfter(this.ul);
        }
        else {
          this.searchAddInput.value = "";
        }
        
        if (this.params.multiple) {
          this.searchAddInput.setStyles({
            top:      this.li.getCoordinates().top + 'px',
            left:     this.li.getCoordinates().right + 'px',
            position: 'absolute'
          });
        }
        else {
          this.searchAddInput.setStyles({
            top:      this.searchAddBtn.getCoordinates().top + 'px',
            left:     this.searchAddBtn.getCoordinates().right + 'px',
            position: 'absolute'
          });
        }
        
        this._lastSearch = "";
        this.searchAddInput.setStyle('display','block');
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
    },
    
    launchSearchAdd: function() {
      if (this._lastSearch!=this.searchAddInput.value) {
        this._lastSearch=this.searchAddInput.value;
        var data = {
          template:   'LSform',
          action:     'LSformElement_select_object_searchAdd',
          attribute:  this.name,
          objecttype: varLSform.objecttype,
          idform:     varLSform.idform,
          pattern:    this.searchAddInput.value
        };
        data.imgload=varLSdefault.loadingImgDisplay(this.searchAddBtn);
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
        this.searchAddUl.setStyles({
          top:      this.searchAddInput.getCoordinates().bottom + 'px',
          left:     this.searchAddInput.getCoordinates().left + 'px',
          position: 'absolute'
        });
        this.searchAddUl.empty();
        if (data.objects) {
          var objs = new Hash(data.objects);
          objs.each(this.addSearchAddLi,this);
        }
        this.searchAddUl.setStyle('display','block');
      }
    },
    
    addSearchAddLi: function(name,dn) {
      var current = 0;
      this.ul.getElements("input[type=hidden]").each(function(input){
        if (input.value==dn) {
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
    
    onSearchAddLiMouseEnter: function(li) {
      li.addClass('LSformElement_select_object_searchAdd_over');
    },
    
    onSearchAddLiMouseLeave: function(li) {
      li.removeClass('LSformElement_select_object_searchAdd_over');
    },
    
    onSearchAddLiClick: function(li) {
      var name = li.innerHTML;
      var dn = li.id;
      this.addLi(name,dn);
    },
    
    closeIfOpenSearchAdd: function(event) {
      event = new Event(event);
      if (this._searchAddOpen == 1 && event.target!=this.searchAddBtn && event.target!=this.searchAddInput && event.target!=this.searchAddUl) {
        this.searchAddInput.setStyle('display','none');
        if ($type(this.searchAddUl)) {
          this.searchAddUl.setStyle('display','none');
        }
        this._searchAddOpen = 0;
      }
    }
});
