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
  var getMotif = /%{([A-Za-z0-9]+)}/
  var find=1;  
  if(($type(data)=='object') || ($type(data)=='array')) {
    if ($type(data[meth])!='function') {
      while (find) {
        var ch = getMotif.exec(format);
        if ($type(ch)) {
          format=format.replace (
                  new RegExp('%{'+ch[1]+'}'),
                  data[ch[1]]
                );
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
            format=format.replace (
                    new RegExp('%{'+ch[1]+'}'),
                    data[meth](ch[1])
                  );
          }
          catch(e) {
            return;
          }
        }
        else {
          find=0;
        }           
      }
    }
  }
  return format;
}
