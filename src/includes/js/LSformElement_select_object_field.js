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
        this.addOrderedBtns(a);
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

    addOrderedBtns: function(a) {
      if (!this.params.ordered) {
        return true;
      }
      var btn_down = new Element('img');
      btn_down.addClass('btn');
      btn_down.setProperties({
        src:    varLSdefault.imagePath('down'),
        alt:    this.params.down_label
      });
      btn_down.addEvent('click',this.onDownBtnClick.bind(this,btn_down));
      btn_down.injectAfter(a);

      var btn_up = new Element('img');
      btn_up.addClass('btn');
      btn_up.setProperties({
        src:    varLSdefault.imagePath('up'),
        alt:    this.params.up_label
      });
      btn_up.addEvent('click',this.onUpBtnClick.bind(this,btn_up));
      btn_up.injectAfter(a);
    },

    onUpBtnClick: function(btn) {
      var li = btn.getParent();
      var prev = li.getPrevious('li');
      if ($type(prev) && !prev.hasClass('LSformElement_select_object_addBtn')) {
        li.inject(prev,'before');
      }
    },

    onDownBtnClick: function(btn) {
      var li = btn.getParent();
      var next = li.getNext('li');
      if ($type(next)) {
        li.inject(next,'after');
      }
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

      selected_objects = {};
      var inputname=this.name+'[]';
      this.ul.getElements('input.LSformElement_select_object').each(function(el) {
        if (el.name==inputname) {
          selected_objects[el.getProperty('value')] = {
            'object_type': el.getProperty('data-object-type'),
          };
        }
      }, this);

      var data = {
        LSselect_id:      this.params['LSselect_id'],
        selected_objects: JSON.encode(selected_objects)
      };

      data.imgload = varLSdefault.loadingImgDisplay(this.addBtn, 'inside');
      new Request({url: 'ajax/class/LSselect/updateSelectedObjects', data: data, onSuccess: this.onAddBtnClickComplete.bind(this)}).send();
    },

    onAddBtnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        varLSsmoothbox.asNew();
        varLSsmoothbox.addEvent('valid',this.onLSsmoothboxValid.bind(this));
        varLSsmoothbox.displayValidBtn();
        var url='object/select/'+this.params['LSselect_id'];
        varLSsmoothbox.openURL(url, {width: 635});
      }
    },

    onLSsmoothboxValid: function() {
      var data = {
        attribute:  this.name,
        objecttype: varLSform.objecttype,
        objectdn:   varLSform.objectdn,
        idform:     varLSform.idform
      };
      data.imgload=varLSdefault.loadingImgDisplay(this.addBtn);
      new Request({url: 'ajax/class/LSformElement_select_object/refresh', data: data, onSuccess: this.onLSsmoothboxValidComplete.bind(this)}).send();
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

    addLi: function(info, dn) {
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
        a.href="object/"+info['object_type']+"/"+dn;
        a.set('html', info['name']);
        a.injectInside(li);

        var input = new Element('input');
        input.setProperties({
          'type':             'hidden',
          'value':            dn,
          'name':             this.name+'[]',
          'data-object-type': info['object_type'],
        });
        input.addClass('LSformElement_select_object');
        input.injectAfter(a);

        this.addOrderedBtns(a);
        this.addDeleteBtn(a);

        li.injectInside(this.ul);
      }
      else { // Non Multiple
        var a = this.ul.getElement('a');
        if ($type(a)) { // Deja initialise
          a.href="object/"+info['object_type']+"/"+dn;
          a.set('html',info['name']);
          a.removeClass('LSformElement_select_object_deleted');

          var input = this.ul.getElement('input');
          input.setProperties({
            'value':            dn,
            'name':             this.name+'[]',
            'data-object-type': info['object_type'],
          });
        }
        else { // Non initialise (No Value)
          this.ul.empty();
          var li = new Element('li');

          var a = new Element('a');
          a.addClass('LSformElement_select_object');
          a.href="object/"+info['object_type']+"/"+dn;
          a.set('html',info['name']);
          a.injectInside(li);

          var input = new Element('input');
          input.setProperties({
            'type':             'hidden',
            'value':            dn,
            'name':             this.name+'[]',
            'data-object-type': info['object_type'],
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
          attribute:  this.name,
          objecttype: varLSform.objecttype,
          idform:     varLSform.idform,
          pattern:    this.searchAddInput.value
        };
        data.imgload=varLSdefault.loadingImgDisplay(this.searchAddInput);
        new Request({url: 'ajax/class/LSformElement_select_object/searchAdd', data: data, onSuccess: this.onSearchAddComplete.bind(this)}).send();
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

    addSearchAddLi: function(info, dn) {
      var current = 0;
      this.ul.getElements("input[type=hidden]").each(function(input){
        if ((input.value==dn)&&(input.name == this.name+'[]')) {
          current=1;
        }
      },this);

      var li = new Element('li');
      li.addClass('LSformElement_select_object_searchAdd');
      li.id = dn;
      li.set('html',info['name']);
      li.setProperties({
        'data-dn': dn,
        'data-object-type': info['object_type'],
      });
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
      this.addLi(
        {
          object_type: li.getProperty('data-object-type'),
          name: li.innerHTML,
        },
        li.getProperty('data-dn')
      );
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
