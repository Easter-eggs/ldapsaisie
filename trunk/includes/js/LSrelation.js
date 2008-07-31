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
      $$('li.LSrelation').each(function(li) {
        this.deleteBtn[this.deleteBtnId] = new Element('img');
        this.deleteBtn[this.deleteBtnId].src = 'templates/images/delete.png';
        this.deleteBtn[this.deleteBtnId].setStyle('cursor','pointer');
        this.deleteBtn[this.deleteBtnId].addClass('LSrelation-btn');
        this.deleteBtn[this.deleteBtnId].addEvent('click',this.onDeleteBtnClick.bind(this,this.deleteBtn[this.deleteBtnId]));
        this.deleteBtn[this.deleteBtnId].injectInside(li);
        li.id=this.deleteBtnId;
        this.deleteBtnId++;
      }, this);
    },
    
    onDeleteBtnClick: function(img) {
      if (this._confirmDelete) {
        var li = img.getParent();
        var span = li.getFirst().getFirst('span');
        this.confirmBox = new LSconfirmBox({
          text:         'Etês-vous sur de vouloir supprimer "'+span.innerHTML+'" ?', 
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
      var span = li.getFirst().getFirst('span');
      var ul = li.getParent();
      img.destroy();
      LSdebug(ul.id);
      var getId = /LSrelation_ul_([0-9]*)/
      var id = getId.exec(ul.id)[1];
      
      var data = {
        template:   'LSrelation',
        action:     'deleteByDn',
        id:         id,
        dn:         span.id
      };
      data.imgload=varLSdefault.loadingImgDisplay(li.id,'inside');
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.deleteFromImgComplete.bind(this)}).send();
    },
    
    deleteFromImgComplete: function(responseText, responseXML) {
      var data = JSON.decode(responseText);
      if ( varLSdefault.checkAjaxReturn(data) ) {
        try  {
          $(data.dn).getParent().getParent().destroy();
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
        varLSsmoothbox.setRefreshElement(this);
        varLSsmoothbox.openURL(data.href,{startElement: $(data.id), width: 615});
      }
    },
    
    refresh: function() {
      var data = {
        template:   'LSrelation',
        action:     'refreshList',
        id:         this.refreshRelation
      };
      
      LSdebug(data);
      data.imgload=varLSdefault.loadingImgDisplay('LSrelation_title_'+this.refreshRelation,'inside');
      new Request({url: 'index_ajax.php', data: data, onSuccess: this.onRrefreshComplete.bind(this)}).send();
    },
    
    onRrefreshComplete: function(responseText, responseXML) {
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
