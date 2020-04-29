var LSformElement_text_field = new Class({
    initialize: function(name,input,parent){
      this._start = false;
      this.name = name;
      this.parent = parent;
      this.input = input;
      this.params = varLSdefault.LSjsConfig[this.name];
      this._auto=1;
      this.onChangeColor = '#f16d6d';
      this.generatedValue = "";
    },

    start: function() {
      if (this._start) {
        return true;
      }
      if ($type(this.params)) {
        if ($type(this.params['generate_value_format'])) {
          this.format = this.params['generate_value_format'];
          this.oldBg=this.input.getStyle('background-color');

          this.fx = new Fx.Tween(this.input,{property: 'background-color',duration:600});

          // GenerateBtn
          this.generateBtn = new Element('img');
          this.generateBtn.addClass('btn');
          this.generateBtn.src=varLSdefault.imagePath('generate');
          this.generateBtn.addEvent('click',this.refreshValue.bind(this,true));
          this.generateBtn.injectAfter(this.input);
          varLSdefault.addHelpInfo(this.generateBtn,'LSformElement_text','generate');

          // Auto
          var force=0;
          if (this.params.autoGenerateOnModify) {
            force = 1;
          }
          this.isCreation = false;
          if (this.input.value=="") {
            this.isCreation = true;
          }

          if (((this.isCreation)&&(this.params.autoGenerateOnCreate))||(force)) {
            this.dependsFields = this.parent.getDependsFields(this.format);
            this.dependsFields.each(function(el) {
              var inputs = varLSform.getInput.bind(this.parent)(el);
              if (inputs.length>0) {
                inputs.each(function(input) {
                  input.addEvent('change',this.refreshValue.bind(this));
                },this);
              }
            },this);
          }
          this._start=true;
        }
      }
    },

    refreshValue: function(force) {
      if (force==true) {
        this._auto=1;
      }
      if (((this._auto)||(force==true))&&((this.generatedValue=="")||(this.generatedValue==this.input.value)||(force==true))) {
        var val=getFData(this.format,varLSform,'getValue');
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
        this.generatedValue = val;
        this.fx.start(this.onChangeColor);
        (function() {this.fx.start(this.oldBg);}).delay(1000,this);
        this.input.fireEvent('change');
      }
    }
});
