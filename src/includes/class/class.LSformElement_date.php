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

    for($i=0;$i<count($data);$i++) {
      if(is_numeric($data[$i])) {
        $data[$i]=strftime($this -> getFormat(),$data[$i]);
      }
      else {
        $this -> form -> setElementError($this -> attr_html);
      }
    }

    $this -> values = $data;
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
      foreach($this -> values as $val) {
        $date = strptime($val,$this -> getFormat());
        if (is_array($date)) {
          $retval[] = mktime($date['tm_hour'],$date['tm_min'],$date['tm_sec'],$date['tm_mon']+1,$date['tm_mday'],$date['tm_year']+1900);
        }
      }
    }
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
      LSsession :: addHelpInfos(
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
