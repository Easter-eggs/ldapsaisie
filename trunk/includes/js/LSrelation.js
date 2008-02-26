var LSrelation = new Class({
    initialize: function(){
      this.edit = 0;
      this.deleteBtn = [];
      this.deleteBtnId = 0;
      this.refreshRelation=0;
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
        el.remove();
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
      li = img.getParent();
      ul = li.getParent();
      img.remove();
      LSdebug(ul.id);
      var getId = /LSrelation_ul_([0-9]*)/
      var id = getId.exec(ul.id)[1];
      
      var data = {
        template:   'LSrelation',
        action:     'deleteByDisplayValue',
        id:         id,
        value:      li.innerHTML
      };
      this.deleteLi = li;
      data.imgload=varLSdefault.loadingImgDisplay(li.id,'inside');
      LSdebug(data);
      new Ajax('index_ajax.php',  {data: data, onComplete: this.onDeleteBtnClickComplete.bind(this)}).request();
    },
    
    onDeleteBtnClickComplete: function(responseText, responseXML) {
      var data = Json.evaluate(responseText);
      LSdebug(data);
      if ( data ) {
        if ( typeof(data.LSerror) != "undefined" ) {
            if (data.imgload!='') {
              varLSdefault.loadingImgHide(data.imgload);
            }
            else {
              varLSdefault.loadingImgHide();
            }
            varLSdefault.displayError(data.LSerror);
            return;
          } 
          else {
            varLSdefault.loadingImgHide(data.imgload);
            this.deleteLi.remove();
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
      new Ajax('index_ajax.php',  {data: data, onComplete: this.onLSrelationModifyBtnClickComplete.bind(this)}).request();
    },
    
    onLSrelationModifyBtnClickComplete: function(responseText, responseXML) {
      var data = Json.evaluate(responseText);
      LSdebug(data);
      if ( data ) {
        if ( typeof(data.LSerror) != "undefined" ) {
            if (data.imgload!='') {
              varLSdefault.loadingImgHide(data.imgload);
            }
            else {
              varLSdefault.loadingImgHide();
            }
            varLSdefault.displayError(data.LSerror);
            return;
          } 
          else {
            varLSdefault.loadingImgHide(data.imgload);
            varLSsmoothbox.openURL(data.href,this);
          }
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
      new Ajax('index_ajax.php',  {data: data, onComplete: this.onRrefreshComplete.bind(this)}).request();
    },
    
    onRrefreshComplete: function(responseText, responseXML) {
      var data = Json.evaluate(responseText);
      LSdebug(data);
      if ( data ) {
        if ( typeof(data.LSerror) != "undefined" ) {
            if (data.imgload!='') {
              varLSdefault.loadingImgHide(data.imgload);
            }
            else {
              varLSdefault.loadingImgHide();
            }
            varLSdefault.displayError(data.LSerror);
            return;
          } 
          else {
            varLSdefault.loadingImgHide(data.imgload);
            $('LSrelation_ul_'+this.refreshRelation).setHTML(data.html);
            this.initializeBtn();
          }
      }
    },
    

});

window.addEvent(window.ie ? 'load' : 'domready', function() {
  varLSrelation = new LSrelation();
});

LSdebug('titi');
