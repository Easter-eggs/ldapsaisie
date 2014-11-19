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

LSsession :: loadLSclass('LSformElement');
LSsession :: loadLSaddon('supann');

/**
 * Element supannLabeledValue d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments supannLabeledValue des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannLabeledValue extends LSformElement {

  var $template = 'LSformElement_supannLabeledValue.tpl';
  var $fieldTemplate = 'LSformElement_supannLabeledValue_field.tpl';
  
  var $supannNomenclatureTable = null;

 /**
  * Retourne les infos d'affichage de l'élément
  * 
  * Cette méthode retourne les informations d'affichage de l'élement
  *
  * @retval array
  */
  function getDisplay(){
    $return = $this -> getLabelInfos();

    $parseValues=array();
    foreach($this -> values as $val) {
      $parseValues[]=$this -> parseValue($val);
    }
    $return['html'] = $this -> fetchTemplate(NULL,array('parseValues' => $parseValues));
    return $return;
  }

 /**
  * Parse une valeur
  *
  * @param[in] $value La valeur
  *
  * @retval array Un tableau cle->valeur contenant value, translated et label
  **/
  function parseValue($value) {
	$retval=array(
		'value' => $value,
	);
	$pv=supannParseLabeledValue($value);
	if ($pv) {
		$retval['label'] = $pv['label'];
		$retval['translated'] = supannGetNomenclatureLabel($this -> supannNomenclatureTable,$pv['label'],$pv['value']);
	}
	else {
		$retval['label'] = 'no';
		$retval['translated'] = getFData(__('%s (Unparsable value)'),$value);
	}
	return $retval;
  }

}

?>
