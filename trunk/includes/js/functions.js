var LSdebug_active = 0;

function LSdebug() {
    if (LSdebug_active != 1) return;
    if (typeof console == 'undefined') return;
    console.log.apply(this, arguments);
}

/**
 * Construction d'une chaine formatée
 *
 * Cette fonction retourne la valeur d'une chaine formatée selon le format
 * et les données passés en paramètre.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 *
 * @param[in] $format string Format de la chaine
 * @param[in] $data mixed Les données pour composés la chaine
 *                    Ce paramètre peut être un tableau de string ou un objet.
 * @param[in] $meth string Le nom de la methode de l'objet(s) à appeler pour
 *                         obtenir la valeur de remplacement dans la chaine formatée.
 * 
 * Exemple d'appel :
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
 * @retval string La chaine formatée
 */
function getFData(format,data,meth) {
  var getMotif =  new RegExp('%\{(([A-Za-z0-9]+)(\:(-?[0-9])+)?(\:(-?[0-9])+)?)\}');
  var find=1;  
  var val="";
  if(($type(data)=='object') || ($type(data)=='array')) {
    if ($type(data[meth])!='function') {
      while (find) {
        var ch = getMotif.exec(format);
        if ($type(ch)) {
          if($type(ch[4])) {
            if ($type(ch[6])) {
              var s=ch[4];
              var l=ch[6];
            }
            else {
              var s=0;
              var l=ch[4];
            }
            var val=data[ch[2]].substr(s,l);
          }
          else {
            val=data[ch[2]];
          }
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
          }
          catch(e) {
            return;
          }
          
          if($type(ch[4])) {
            if ($type(ch[6])) {
              var s=ch[4];
              var l=ch[6];
            }
            else {
              var s=0;
              var l=ch[4];
            }
            val=val.substr(s,l);
          }
          
          format=format.replace(new RegExp('%\{'+ch[1]+'\}'),val);
        }
        else {
          find=0;
        }           
      }
    }
  }
  return format;
}

/**
* Supprime les accents d'une chaine
* 
* @param[in] $string La chaine originale
* 
* @retval string La chaine sans les accents
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
* Remplace les espaces ou les tabulations d'une chaine
* 
* @param[in] $string La chaine originale
* @param[in] $string Le caractère à mettre à la place
* 
* @retval string La chaine sans espace
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
