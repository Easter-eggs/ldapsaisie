<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * http://ldapsaisie.labs.libre-entreprise.org
 *
 * Author: See AUTHORS file in top-level directory.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

******************************************************************************/


/**
 * Element date d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments dates des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_date extends LSformElement {

  var $fieldTemplate = 'LSformElement_date_field.tpl';

  var $_php2js_format = array(
    "a" => "a",
    "A" => "A",
    "b" => "b",
    "B" => "B",
    "c" => "c",
    "d" => "d",
    "e" => "e",
    "H" => "H",
    "I" => "I",
    "j" => "j",
    "m" => "m",
    "M" => "M",
    "p" => "p",
    "s" => "s",
    "S" => "S",
    "T" => "T",
    "U" => "U",
    "w" => "w",
    "y" => "y",
    "Y" => "Y",
    "z" => "z",
    "Z" => "Z",
    "%" => "%",
  );

  var $_cache_php2js_format=array();

  var $default_style="vista";

  /**
   * Définis la valeur de l'élément date
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] [<b>required</b>] string or array La futur valeur de l'élément
   *
   * @retval boolean Retourne True
   */
  public function setValue($data) {
    if (!is_array($data)) {
      $data=array($data);
    }
    $special_values = $this -> getSpecialValues();
    $values = array();
    foreach ($data as $value) {
      if(is_numeric($value)) {
        if (array_key_exists($value, $special_values)) {
          $values[] = $special_values[$value];
          continue;
        }
        $values[] = strftime($this -> getFormat(), $value);
      }
      else {
        $this -> form -> setElementError($this -> attr_html);
        return false;
      }
    }
    self :: log_trace("$this -> setValue():".varDump($data)." => ".varDump($values));
    $this -> values = $values;
    return true;
  }

  /**
   * Exporte les valeurs de l'élément
   *
   * @retval Array Les valeurs de l'élement
   */
  public function exportValues(){
    $retval=array();
    if (is_array($this -> values)) {
      $special_values = $this -> getSpecialValues();
      foreach($this -> values as $val) {
        if (array_key_exists($val, $special_values)) {
          $retval[] = $val;
          continue;
        }
        $date = strptime($val, $this -> getFormat());
        if (is_array($date)) {
          $retval[] = mktime($date['tm_hour'],$date['tm_min'],$date['tm_sec'],$date['tm_mon']+1,$date['tm_mday'],$date['tm_year']+1900);
        }
        else {
          self :: log_warning("exportValues($val): Fail to parse value from form.");
        }
      }
    }
    self :: log_trace("$this -> exportValues():".varDump($this -> values)." => ".varDump($retval));
    return $retval;
  }

 /**
  * Retourne le format d'affichage de la date
  *
  * @retval string Le format de la date
  **/
  public function getFormat() {
    return $this -> getParam('html_options.format', ($this -> getParam('html_options.time', true)?'%d/%m/%Y, %T':'%d/%m/%Y'));
  }

 /**
  * Return date picker style value
  *
  * @retval string The date picker style
  **/
  public function getStyle() {
    $style = $this -> getParam('html_options.style', $this -> default_style, 'string');
    if ($style) {
      if (is_dir(LS_LIB_DIR.'arian-mootools-datepicker/datepicker_'.$style)) {
        return $style;
      }
      LSdebug('LSformElement :: Date => unknown style parameter value '.$style);
    }
    return $this -> default_style;
  }

 /**
  * Retourne les infos d'affichage de l'élément
  *
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  public function getDisplay(){
    $return = $this -> getLabelInfos();
    // value
    if (!$this -> isFreeze()) {
      // Help Infos
      LStemplate :: addHelpInfo(
        'LSformElement_date',
        array(
          'now' => _('Now.'),
          'today' => _('Today.')
        )
      );

      $params = array(
        'format' => $this -> php2js_format($this -> getFormat()),
        'style' => $this -> getStyle(),
        'time' => $this -> getParam('html_options.time', true, 'bool'),
        'manual' => $this -> getParam('html_options.manual', true, 'bool'),
        'showNowButton' => $this -> getParam('html_options.showNowButton', true, 'bool'),
        'showTodayButton' => $this -> getParam('html_options.showTodayButton', true, 'bool'),
      );
      LStemplate :: addJSconfigParam($this -> name, $params);

      $codeLang = str_replace('_','-',preg_replace('/\..*$/','', LSlang :: getLang()));

      LStemplate :: addLibJSscript('arian-mootools-datepicker/Picker.js');
      LStemplate :: addLibJSscript('arian-mootools-datepicker/Picker.Attach.js');
      LStemplate :: addLibJSscript('arian-mootools-datepicker/Picker.Date.js');
      LStemplate :: addLibJSscript('arian-mootools-datepicker/Locale.'.$codeLang.'.DatePicker.js');
      LStemplate :: addLibCssFile('arian-mootools-datepicker/datepicker_'.$params['style'].'/datepicker_'.$params['style'].'.css');

      LStemplate :: addJSscript('LSformElement_date_field.js');
      LStemplate :: addJSscript('LSformElement_date.js');
    }

    $return['html'] = $this -> fetchTemplate();
    return $return;
  }

  /**
   * Retournne un template Smarty compilé dans le contexte d'un LSformElement
   *
   * @param[in] string $template Le template à retourner
   * @param[in] array $variables Variables Smarty à assigner avant l'affichage
   *
   * @retval string Le HTML compilé du template
   */
   public function fetchTemplate($template=NULL, $variables=array()) {
     $variables['special_values'] = $this -> getSpecialValues();
     return parent :: fetchTemplate($template, $variables);
   }

  /**
   * Recupère la valeur de l'élement passée en POST
   *
   * Cette méthode vérifie la présence en POST de la valeur de l'élément et la récupère
   * pour la mettre dans le tableau passer en paramètre avec en clef le nom de l'élément
   *
   * @param[in] &$return array Reference of the array for retreived values
   * @param[in] $onlyIfPresent boolean If true and data of this element is not present in POST data,
   *                                   just ignore it.
   *
   * @retval boolean true si la valeur est présente en POST, false sinon
   */
  public function getPostData(&$return, $onlyIfPresent=false) {
    if($this -> isFreeze()) {
      return true;
    }
    $values = self :: getData($_POST, $this -> name);
    $special_values = self :: getData($_POST, $this -> name.'__special_value');
    self :: log_trace($this." -> getPostData(): values=".varDump($values));
    self :: log_trace($this." -> getPostData(): special_values=".varDump($special_values));
    if (!is_array($values) && !is_array($special_values)) {
      self :: log_trace($this." -> getPostData(): not in POST data");
      if ($onlyIfPresent) {
        self :: log_debug($this -> name.": not in POST data => ignore it");
      }
      else {
        $return[$this -> name] = array();
      }
    }
    else {
      if(!is_array($values))
        $values = array();
      if(!is_array($special_values))
        $special_values = array();
      $return[$this -> name] = $special_values + $values;
      self :: log_trace($this." -> merged values=".varDump($return[$this -> name]));
    }
    return true;
  }

  /**
   * Retrieve list of special values with translated labels
   *
   * @return array Associative array with special values as keys and corresponding translated labels as values
   */
  public function getSpecialValues() {
    $special_values = array();
    foreach ($this -> getParam('html_options.special_values', array()) as $value => $label)
      $special_values[strval($value)] = __($label);
    self :: log_trace("getSpecialValues(): ".varDump($special_values));
    return $special_values;
  }

 /**
  * Convertis un format de date Php (strftime) en JS (jscalendar)
  *
  * @retval mixed Format de date jscalendar (string) ou False si la convertion
  *               n'a pas réussi.
  */
  public function php2js_format($format) {
    if (isset($this -> _cache_php2js_format[$format])) {
      return $this -> _cache_php2js_format[$format];
    }
    $new="";
    for($i=0;$i<strlen($format);$i++) {
      if ($format[$i]=="%") {
        if (isset($this -> _php2js_format[$format[$i+1]])) {
          $new.="%".$this -> _php2js_format[$format[$i+1]];
          $i++;
        }
        else {
          $this -> _cache_php2js_format[$format]=false;
          return;
        }
      }
      else {
        $new.=$format[$i];
      }
    }
    $this -> _cache_php2js_format[$format]=$new;
    return $new;
  }
}
