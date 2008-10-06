var LSformElement_text_field = new Class({
    initialize: function(name,input,parent){
      this.name = name;
      this.parent = parent;
      this.input = input;
      this.params = varLSdefault.LSjsConfig[this.name];
      this._auto=1;
      this.input.addEvent('change',this.unauto.bind(this));
      this.onChangeColor = '#f16d6d';
    },
    
    start: function() {
      var force=0;
      if ($type(this.params)) {
        if (this.params.autoGenerateOnModify) {
          force = 1;
        }
      }
      if ((this.input.value=='')||(force)) {
        if ($type(this.params)) {
          if ($type(this.params['generate_value_format'])) {
            this.format = this.params['generate_value_format'];
            this.dependsFields = this.parent.getDependsFields(this.format);
            this.dependsFields.each(function(el) {
              var input = this.parent.getInput.bind(this.parent)(el);
              input.addEvent('change',this.refreshValue.bind(this));
            },this);
            this.oldBg=this.input.getStyle('background-color');
            this.fx = new Fx.Tween(this.input,{property: 'background-color',duration:600});
            this.generateBtn = new Element('img');
            this.generateBtn.addClass('btn');
            this.generateBtn.src='templates/images/generate.png';
            this.generateBtn.addEvent('click',this.refreshValue.bind(this));
            this.generateBtn.injectAfter(this.input);
          }
        }
      }
    },
    
    getInput: function() {
      return this.input;
    },
    
    getValue: function() {
      return this.input.value;
    },
    
    refreshValue: function() {
      if (this._auto) {
        var val=getFData(this.format,this.parent,'getValue');
        if ($type(this.params['withoutAccents'])) {
          if(this.params['withoutAccents']) {
            val = replaceAccents(val);
          }
        }
        this.input.value = val;
        this.fx.start(this.onChangeColor);
        (function() {this.fx.start(this.oldBg);}).delay(1000,this);
      }
    },
    
    unauto: function() {
      this._auto=0;
    }
});
