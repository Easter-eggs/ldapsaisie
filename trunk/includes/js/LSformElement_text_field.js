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
      if ($type(this.params)) {
        if ($type(this.params['generate_value_format'])) {
          this.format = this.params['generate_value_format'];
          this.oldBg=this.input.getStyle('background-color');
          
          this.fx = new Fx.Tween(this.input,{property: 'background-color',duration:600});
          
          // GenerateBtn
          this.generateBtn = new Element('img');
          this.generateBtn.addClass('btn');
          this.generateBtn.src=varLSdefault.imagePath('generate.png');
          this.generateBtn.addEvent('click',this.refreshValue.bind(this));
          this.generateBtn.injectAfter(this.input);

          // Auto
          var force=0;
          if (this.params.autoGenerateOnModify) {
            force = 1;
          }
          if ((this.input.value=='')||(force)) {
            this.dependsFields = this.parent.getDependsFields(this.format);
            this.dependsFields.each(function(el) {
              var input = this.parent.getInput.bind(this.parent)(el);
              input.addEvent('change',this.refreshValue.bind(this));
            },this);
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
        if ($type(this.params['withoutAccent'])) {
          if(this.params['withoutAccent']) {
            val = replaceAccents(val);
          }
        }
        if ($type(this.params['replaceSpaces'])) {
          if(this.params['replaceSpaces']) {
            val = replaceSpaces(val,this.params['replaceSpaces']);
          }
        }
        if ($type(this.params['upperCase'])) {
          if(this.params['upperCase']) {
            val = val.toUpperCase();
          }
        }
        if ($type(this.params['lowerCase'])) {
          if(this.params['lowerCase']) {
            val = val.toLowerCase();
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
