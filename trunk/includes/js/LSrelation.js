var LSrelation = new Class({
    initialize: function(){
      this.edit = 0;
      this.deleteBtn = [];
      this.deleteBtnId = 0;
      this.refreshRelation=0;
      this._confirmDelete=1;
      $$('a.LSrelation_modify').each(function(el) {
        this.edit=1;
        el.addEvent('click',this.onLSrelationModifyBtnClick.bindWithEvent(this,el));
      }, this);
      if (this.edit) {
        this.initializeBtn();
      }
    },
    
    initializeBtn: function() {
      $$('img.LSrelation-btn').each(function(el) {
        el.destroy();
      }, this);
      this.deleteBtnId = 0;
      $$('a.LSrelation').each(function(a) {
        this.deleteBtn[this.deleteBtnId] = new Element('img');
        this.deleteBtn[this.deleteBtnId].src = varLSdefault.imagePath('delete.png');
        this.deleteBtn[this.deleteBtnId].setStyle('cursor','pointer');
        this.deleteBtn[this.deleteBtnId].addClass('LSrelation-btn');
        this.deleteBtn[this.deleteBtnId].addEvent('click',this.onDeleteBtnClick.bind(this,this.deleteBtn[this.deleteBtnId]));
        this.deleteBtn[this.deleteBtnId].injectAfter(a);
        a.getParent().id=this.deleteBtnId;
        this.deleteBtnId++;
      }, this);
    },
    
    onDeleteBtnClick: function(img) {
      if (this._confirmDelete) {
        var a = img.getPrevious('a');
        this.confirmBox = new LSconfirmBox({
          text:         'Etês-vous sur de vouloir supprimer "'+a.innerHTML+'" ?', 
          startElement: img,
          onConfirm:    this.deleteFromImg.bind(this,img)
        });
      }
      else {
        this.deleteFromImg(img);
      }
    },
    
    deleteFromImg: function(img) {
      var li = img.getParent();
      var a = img.getPrevious('a');
      var ul = li.getParent();
      img.destroy();
      LSdebug(ul.id);
      var getId = /LSrelation_ul_([0-9]*)/
      var id = getId.exec(ul.id)[1];
      
      var data = {
        template:   'LSrelation',
        action:     'deleteByDn',
        id:         id,
        dn:         a.id
      };
      data.imgload=varLSdefault.loadingImgDisplay(li,'inside');
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.deleteFromImgComplete.bind(this)}).send();
    },
    
    deleteFromImgComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        try  {
          var li = $(data.dn).getParent();
          var ul=$(data.dn).getParent().getParent();
          li.destroy();
          if (!$type(ul.getFirst())) {
            var getId = /LSrelation_ul_([0-9]*)/
            var id = getId.exec(ul.id)[1];
            
            var newli = new Element('li');
            newli.addClass('LSrelation');
            newli.set('html',varLSdefault.LSjsConfig['LSrelations'][id]['emptyText']);
            newli.injectInside(ul);
          }
        }
        catch(e) {
          LSdebug('Erreur durant la suppression du li du DN : '+data.dn);
        }
      }
    },
    
    onLSrelationModifyBtnClick: function(event,a) {
      new Event(event).stop();
      
      var data = {
        template:   'LSrelation',
        action:     'refreshSession',
        id:         a.id,
        href:       a.href
      };
      
      LSdebug(data);
      this.refreshRelation=a.id;
      data.imgload=varLSdefault.loadingImgDisplay('LSrelation_title_'+a.id,'inside');
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onLSrelationModifyBtnClickComplete.bind(this)}).send();
    },
    
    onLSrelationModifyBtnClickComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        varLSsmoothbox.asNew();
        varLSsmoothbox.addEvent('valid',this.onLSsmoothboxValid.bind(this));
        varLSsmoothbox.openURL(data.href,{startElement: $(data.id), width: 615});
      }
    },
    
    onLSsmoothboxValid: function() {
      var data = {
        template:   'LSrelation',
        action:     'refreshList',
        id:         this.refreshRelation
      };
      
      LSdebug(data);
      data.imgload=varLSdefault.loadingImgDisplay('LSrelation_title_'+this.refreshRelation,'inside');
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onLSsmoothboxValidComplete.bind(this)}).send();
    },
    
    onLSsmoothboxValidComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        $('LSrelation_ul_'+this.refreshRelation).set('html',data.html);
        this.initializeBtn();
      }
    }

});

window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSrelation = new LSrelation();
});
