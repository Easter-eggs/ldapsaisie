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
    "C" => "C",
    "d" => "d",
    "D" => "m/%d/%y",
    "e" => "e",
    "h" => "b",
    "H" => "H",
    "I" => "I",
    "j" => "j",
    "m" => "m",
    "M" => "M",
    "n" => "n",
    "p" => "p",
    "r" => "p",
    "R" => "H:%M",
    "S" => "S",
    "t" => "t",
    "T" => "H:%M:%S",
    "u" => "u",
    "U" => "U",
    "V" => "V",
    "w" => "w",
    "W" => "W",
    "y" => "y",
    "Y" => "Y",
    "Z" => "T",
    "%" => "%"
  );
  
  var $_cache_php2js_format=array();

  /**
   * Définis la valeur de l'élément date
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] [<b>required</b>] string or array La futur valeur de l'élément
   *
   * @retval boolean Retourne True
   */
  function setValue($data) {
    if (!is_array($data)) {
      $data=array($data);
    }
    
    for($i=0;$i<count($data);$i++) {
      $data[$i]=strftime($this -> getFormat(),$data[$i]);
    }

    $this -> values = $data;
    return true;
  }
  
  /**
   * Exporte les valeurs de l'élément
   * 
   * @retval Array Les valeurs de l'élement
   */
  function exportValues(){
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
  function getFormat() {
    if (isset($this -> params['html_options']['format'])) {
      return $this -> params['html_options']['format'];
    }
    else {
      return "%d/%m/%Y, %T";
    }
  }

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $return = $this -> getLabelInfos();
    // value
    if (!$this -> isFreeze()) {
      // Help Infos
      LSsession :: addHelpInfos(
        'LSformElement_date',
        array(
          'calendar' => _('Sélectionner dans un calendrier.'),
          'now' => _('Maintenant.')
        )
      );
      
      $params = array(
        'format' => $this -> php2js_format($this -> getFormat()),
        'firstDayOfWeek' => $this -> getFirstDayOfWeek()
      );
      LSsession :: addJSconfigParam($this -> name,$params);
      
      LSsession :: addCssFile('theme.css',LS_LIB_DIR.'jscalendar/skins/aqua/');
      LSsession :: addJSscript('calendar.js',LS_LIB_DIR.'jscalendar/');
      LSsession :: addJSscript('calendar-en.js',LS_LIB_DIR.'jscalendar/lang/');
      $codeLang = strtolower($GLOBALS['LSconfig']['lang'][0].$GLOBALS['LSconfig']['lang'][1]);
      LSsession :: addJSscript('calendar-'.$codeLang.'.js',LS_LIB_DIR.'jscalendar/lang/');
      LSsession :: addJSscript('LSformElement_date_field.js');
      LSsession :: addJSscript('LSformElement_date.js');
    }
    $return['html'] = $this -> fetchTemplate();
    return $return;
  }
 
 /**
  * Retourne le nurméro du premier jour de la semaine
  * 
  * @retval int 0=dimanche ... 6=samedi, par défaut 0=dimanche
  */
  function getFirstDayOfWeek() {
    if (isset($this -> params['html_options']['firstDayOfWeek'])) {
      return $this -> params['html_options']['firstDayOfWeek'];
    }
    else {
      return 0;
    }
  }
  
 /**
  * Convertis un format de date Php (strftime) en JS (jscalendar)
  * 
  * @retval mixed Format de date jscalendar (string) ou False si la convertion
  *               n'a pas réussi.
  */
  function php2js_format($format) {
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

?>
