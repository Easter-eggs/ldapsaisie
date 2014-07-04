var LSdebug_active = 0;

function LSdebug(arguments) {
    if (LSdebug_active != 1) return;
    if (typeof console != 'undefined') {
      console.log(arguments);
      return true;
    }
    if (typeof opera != 'undefined') {
      opera.postError(arguments);
      return true;
    }
    if (typeof varLSdefault != 'undefined') {
      varLSdefault.log(arguments);
      return true;
    }
    alert(arguments);
}

/**
 * Construction of formatted string
 *
 * This function returns a formatted string according to given data & format parameters
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $format string String Format
 * @param[in] $data mixed Data used to compose the string.
 *                    It can be strings array or object.
 * @param[in] $meth string Object method name to call to get the new value for the formatted string.
 * 
 * Invocation example :
 * getFData('%{test1} je  %{test2}',{
 *    getValue: function(val) {
 *      var data = {
 *        test1: 'val_test1',
 *        test2: 'val_test2'
 *      };
 *      return data[val];
 *    }
 * },'getValue');
 * 
 * @retval string The formatted string
 */
function getFData(format,data,meth) {
  /*
   * Format : %{[key name][:A][:B][! ou _][~]}
   *
   * Extracted fields
   * - 1 : full string in %{}
   * - 2 : key name
   * - 3 : :A
   * - 4 : A
   * - 5 : :B
   * - 6 : B
   * - 7 : "-"
   * - 8 : ! or _
   * - 9 : ~
   */
  var getMotif =  new RegExp('%\{(([A-Za-z0-9]+)(\:(-?[0-9])+)?(\:(-?[0-9])+)?)(-)?(\!|\_)?(~)?\}');
  var find=1;  
  var val="";
  if(($type(data)=='object') || ($type(data)=='array')) {
    if ($type(data[meth])!='function') {
      while (find) {
        var ch = getMotif.exec(format);
        if ($type(ch)) {
          val=_getFData_extractAndModify(data[ch[2]],ch);
          format=format.replace(new RegExp('%\{'+ch[1]+'\}'),val);
        }
        else {
          find=0;
        }           
      }
    }
    else {
      while (find) {
        var ch = getMotif.exec(format);
        if ($type(ch)) {
          try {
            val=data[meth](ch[2]);
            if ($type(val)=='array') {
              if (val.length==0) {
                val=''
              }
              else {
                val=val[0];
              }
            }
          }
          catch(e) {
            LSdebug('getFData() : '+meth+'() -> rater');
            return;
          }
          
          val=_getFData_extractAndModify(val,ch);
          
          format=format.replace(new RegExp('%\{'+ch[1]+'[\:0-9\!\_\~\-]*\}'),val);
        }
        else {
          find=0;
        }           
      }
    }
  }
  else if(($type(data)=='string')) {
    while (find) {
      var ch = getMotif.exec(format);
      if ($type(ch)) {
        val=_getFData_extractAndModify(data,ch)
        format=format.replace(new RegExp('%\{'+ch[1]+'[\:0-9\!\_\~\-]*\}'),val);
      }
      else {
        find=0;
      }
    }
  }
  return format;
}

function _getFData_extractAndModify(data,ch) {
  console.log(ch);
  var val=data;
  // If A
  if($type(ch[4])) {
    ch[4]=parseInt(ch[4]);
    var s=0;
    var l=data.length;
    if ($type(ch[6])) {
      ch[6]=parseInt(ch[6]);
      // With A and B
      if (ch[6]==0) {
        // If B == 0
        ch[6]=data.length;
      }
      if (ch[4]>0) {
        // A > 0
        s=ch[4];
        l=ch[6];
      }
      else {
        // A < 0
        s=data.length+ch[4];
        if (ch[6]<0) {
          // B < 0
          l=data.length-s+ch[6];
        }
        else {
          // B > 0
          l=ch[6];
        }
      }
    }
    else {
      // Only A
      if (ch[4]>0) {
        // A > 0
        s=0;
        l=ch[4];
      }
      else {
        // A < 0
        s=data.length+ch[4];
        l=data.length;
      }
    }
    console.log("s = " + s + " / l = " + l);
    val=data.substr(s,l);
  }
  // Upper or Lower case
  if (ch[8]=='!') {
    val=val.toUpperCase();
  }
  else if (ch[8]=='_') {
    val=val.toLowerCase();
  }
  // Strip accents
  if (ch[9]=='~') {
    val=replaceAccents(val);
  }
  return val;
}

/**
* Delete accentuated characters in a string
* 
* @param[in] $string Original string
* 
* @retval string de-accentuated string
*/
function replaceAccents(str) {
  var new_str = String(str);
  var accent = 
    new Array("à","á","â","ã","ä","ç","è","é","ê","ë","ì","í","î","ï","ñ","ò","ó","ô","õ","ö","ù","ú","û","ü","ý","ÿ","À","Á","Â","Ã","Ä","Ç","È","É","Ê","Ë","Ì","Í","Î","Ï","Ñ","Ò","Ó","Ô","Õ","Ö","Ù","Ú","Û","Ü","Ý");
  var sans_accent = 
    new Array("a","a","a","a","a","c","e","e","e","e","i","i","i","i","n","o","o","o","o","o","u","u","u","u","y","y","A","A","A","A","A","C","E","E","E","E","I","I","I","I","N","O","O","O","O","O","U","U","U","U","Y");
  if (str && str!= "") {
    for (i=0; i<accent.length; i++) {
      var reg_exp= RegExp(accent[i], "gi");
      new_str = new_str.replace (reg_exp, sans_accent[i]);
    }
  }
  return new_str;
}

/**
* Replace spaces or tabs of a string by an argument
* 
* @param[in] $string The original string
* @param[in] $string The character to set instead of spaces or tabs
* 
* @retval string The modified outspaced string
*/
function replaceSpaces(str,to) {
  if (!$type(to)) {
    to = '';
  }
  var new_str = String(str);
  if (str && str!= "") {
    var reg_exp= RegExp('[ \t]', "gi");
    new_str = new_str.replace (reg_exp, to);
  }
  return new_str;
}

/**
 * Add one variable with value in URL
 * @param[in] url string The original URL
 * @param[in] name string The variable name
 * @param[in] value string The value of the variable
 * 
 * @retval string The URL with the value
 */
function urlAddVar(url,name,value) {
  if ($type(url)) {
    var isExtended = RegExp('[?]');
    if (isExtended.test(url)) {
      url=url+'&';
    }
    else {
      url=url+'?';
    }
    return url + name + '=' + value;
  }
  return url;
}
