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
 * Element d'un formulaire pour LdapSaisie
 *
 * Cette classe g�re les �l�ments des formulaires.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement {

  var $name;
  var $label;
  var $params;
  var $values = array();
  var $_required = false;
  var $_freeze = false;

  /**
   * Constructeur
   *
   * Cette methode construit l'objet et d�finis sa configuration de base.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] &$form [<b>required</b>] LSform L'objet LSform parent
   * @param[in] $name [<b>required</b>] string Le nom de r�f�rence de l'�l�ment
   * @param[in] $label [<b>required</b>] string Le label de l'�l�ment
   * @param[in] $params mixed Param�tres suppl�mentaires
   *
   * @retval true
   */	
	function LSformElement (&$form, $name, $label, $params){
    $this -> name = $name;
		$this -> label = $label;
		$this -> params = $params;
		$this -> form = $form;
	 	return true;
  }

  /**
   * D�finis la valeur de l'�l�ment
   *
   * Cette m�thode d�finis la valeur de l'�l�ment
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] [<b>required</b>] string or array La futur valeur de l'�l�ment
   *
   * @retval boolean Retourne True
   */
  function setValue($data) {
		if (!is_array($data)) {
			$data=array($data);
		}

		$this -> values = $data;
		return true;
  }

	/**
   * Ajoute une valeur � l'�l�ment
   *
   * Cette m�thode ajoute une valeur � l'�l�ment
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] [<b>required</b>] string or array La futur valeur de l'�l�ment
   *
   * @retval void
   */
  function addValue($data) {
		if (is_array($data)) {
			$this -> values = array_merge($this -> values, $data);
		}
		else {
			$this -> values[] = $data;
		}
  }

	/**
	 * Test si l'�l�ment est �ditable
	 * 
	 * Cette m�thode test si l'�l�ment est �ditable
	 *
	 * @retval boolean
	 */
	function isFreeze(){
		return $this -> _freeze;
	}
  
  /*
   * Freeze l'�l�ment
   *
   * Rend l'�l�ment non-editable
   *
   * @retval void
   */
  function freeze() {
		$this -> _freeze = true;
  }

  /*
   * D�fini la propri�t� required de l'�l�ment.
   *
   * param[in] $isRequired boolean true si l'�l�ment est requis, false sinon
   *
   * @retval void
   */
  function setRequired($isRequired=true) {
		$this -> _required = $isRequired;
  }

	/*
	 * Test si l'�l�ment est requis
	 * 
	 * Cette m�thode test si l'�l�ment est requis
	 *
	 * @retval boolean
	 */
	function isRequired(){
		return $this -> _required;
	}

	/**
	 * Affiche le label de l'�lement
	 *
	 * @retval void
	 */
	function displayLabel() {
   	if ($this -> isRequired()) {
      	$required=" <span class='required_elements'>*</span>";
   	}
	  else {
	      $required="";
   	}
	  echo "\t\t<td>".$this -> getLabel()."$required</td>\n";
	}

	/**
	 * Retourne le label de l'�lement
	 *
	 * @retval void
	 */
	function getLabelInfos() {
   	if ($this -> isRequired()) {
      	$return['required']=true;
   	}
	  $return['label'] = $this -> getLabel();
		return $return;
	}

	/**
	 * Recup�re la valeur de l'�lement pass�e en POST
	 *
	 * Cette m�thode v�rifie la pr�sence en POST de la valeur de l'�l�ment et la r�cup�re
	 * pour la mettre dans le tableau passer en param�tre avec en clef le nom de l'�l�ment
	 *
	 * @param[] array Pointeur sur le tableau qui recup�rera la valeur.
	 *
	 * @retval boolean true si la valeur est pr�sente en POST, false sinon
	 */
	function getPostData(&$return) {
		if($this -> params['form'][$this -> form -> idForm] != 1) {
			return true;
		}
		if (isset($_POST[$this -> name])) {
			if(!is_array($_POST[$this -> name])) {
				$_POST[$this -> name] = array($_POST[$this -> name]);
			}
			foreach($_POST[$this -> name] as $key => $val) {
					$return[$this -> name][$key] = $val;
			}
			return true;
		}
		else {
			$return[$this -> name] = array();
			return true;
		}
	}

	/**
	 * Retourne le label de l'�lement
	 *
	 * Retourne $this -> label, ou $this -> params['label'], ou $this -> name
	 *
	 * @retval string Le label de l'�l�ment
	 */
	function getLabel() {
		if ($this -> label != "") {
			return $this -> label;
		}
		else if ($this -> params['label']) {
			return $this -> params['label'];
		}
		else {
			return $this -> name;
		}
	}

	/**
	 * Retourne l'HTML pour les boutons d'ajout et de suppression de champs du formulaire LSform
	 *
	 * @retval string Le code HTML des boutons
	 */
	function getMultipleData() {
		if ($this -> params['multiple'] == true ) {
			return "<img src='templates/images/add.png' id='LSform_add_field_btn_".$this -> name."_".rand()."' class='LSform-add-field-btn' alt='"._('Ajouter')."'/><img src='templates/images/remove.png' class='LSform-remove-field-btn' alt='"._('Supprimer')."'/>";
		}
		else {
			return '';
		}
	}
}

?>
