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
 * Formulaire pour LdapSaisie
 *
 * Cette classe g�re les formulaires
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSform {
  var $ldapObject;
  var $idForm;
  var $can_validate = true;
  var $elements = array();
  var $_rules = array();

  var $_postData = array();
 
  var $_elementsErrors = array();
  var $_isValidate = false;

  var $_notUpdate = array();

  /**
   * Constructeur
   *
   * Cette methode construit l'objet et d�finis la configuration.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $idForm [<b>required</b>] string L'identifiant du formulaire
   * @param[in] $submit string La valeur du bouton submit
   *
   * @retval void
   */ 
  function LSform (&$ldapObject,$idForm,$submit="Envoyer"){
    $this -> idForm = $idForm;
    $this -> submit = $submit;
    $this -> ldapObject = $ldapObject;
    $GLOBALS['LSsession'] -> loadLSclass('LSformElement');
  }
  
  /**
   * Affiche le formualaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */ 
  function display(){
    if ($this -> idForm == 'view') {
      $GLOBALS['LSsession'] -> addJSscript('LSview.js');
    }
    else {
      $GLOBALS['LSsession'] -> addJSscript('LSform.js');
    }
    $GLOBALS['LSsession'] -> addCssFile('LSform.css');
    $GLOBALS['Smarty'] -> assign('LSform_action',$_SERVER['PHP_SELF']);
    $LSform_header = "\t<input type='hidden' name='validate' value='LSform'/>\n
    \t<input type='hidden' name='idForm' id='LSform_idform' value='".$this -> idForm."'/>\n
    \t<input type='hidden' name='LSform_objecttype' id='LSform_objecttype'  value='".$this -> ldapObject -> getType()."'/>\n
    \t<input type='hidden' name='LSform_objectdn' id='LSform_objectdn'  value='".$this -> ldapObject -> getValue('dn')."'/>";
    $GLOBALS['Smarty'] -> assign('LSform_header',$LSform_header);
    $LSform_object = array(
      'type' => $this -> ldapObject -> getType(),
      'dn' => $this -> ldapObject -> getDn()
    );
    $GLOBALS['Smarty'] -> assign('LSform_object',$LSform_object);
    $fields = array();
    foreach($this -> elements as $element) {
      $field = array();
      $field = $element -> getDisplay();
      if (isset($this -> _elementsErrors[$element -> name])) {
        $field['errors']= $this -> _elementsErrors[$element -> name];
      }
      $fields[] = $field;
    }
    $GLOBALS['Smarty'] -> assign('LSform_fields',$fields);
    if($this -> can_validate) {
      $GLOBALS['Smarty'] -> assign('LSform_submittxt',$this -> submit);
    }
  }
  
  /**
   * Affiche la vue
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */ 
  function displayView(){
    $GLOBALS['LSsession'] -> addCssFile('LSform.css');
    $LSform_object = array(
      'type' => $this -> ldapObject -> getType(),
      'dn' => $this -> ldapObject -> getDn()
    );
    $GLOBALS['Smarty'] -> assign('LSform_object',$LSform_object);
    $fields = array();
    foreach($this -> elements as $element) {
      $field = $element -> getDisplay();
      $fields[] = $field;
    }
    $GLOBALS['Smarty'] -> assign('LSform_fields',$fields);
  }  
  
  /**
   * D�fini l'erreur sur un champ
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $attr [<b>required</b>] string Le nom du champ
   * @param[in] $msg Le format du message d'erreur � afficher (pouvant comporter
   *                 des valeurs %{[n'importe quoi]} qui seront remplac� par le label
   *                 du champs concern�.
   *
   * @retval void
   */ 
  function setElementError($attr,$msg=NULL) {
    if($msg!='') {
      $msg_error=getFData($msg,$attr->getLabel());
    }
    else {
      $msg_error=getFData(_("Les donn�es pour l'attribut %{label} ne sont pas valides."),$attr->getLabel());
    }
    $this -> _elementsErrors[$attr->name][]=$msg_error;
  }
  
  /**
   * Verifie si le formulaire a �t� valid� et que les donn�es sont valides.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si le formulaire a �t� valid� et que les donn�es ont �t� valid�es, false sinon
   */ 
  function validate(){
    if(!$this -> can_validate)
      return;
    if ($this -> isSubmit()) {
      if (!$this -> getPostData()) {
        $GLOBALS['LSerror'] -> addErrorCode(201);
        return;
      }
      //Validation des donn�es ici !!! ///
      if (!$this -> checkData()) {
        $this -> setValuesFromPostData();
        return;
      }
      debug("les donn�es sont check�es");
      $this -> _isValidate = true;
      return true;
    }
    return false;
  }

  /**
   * V�rifier les donn�es du formulaire � partir des r�gles d�finis sur les champs
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si toutes la saisie est OK, false sinon
   */
  function checkData() {
    $retval=true;
    foreach ($this -> _postData as $element => $values) {
      if(!is_array($values)) {
        $values=array($values);
      }
      if ($this -> elements[$element] -> isRequired()) {
        if (!$this -> checkRequired($values)) {
          $this -> setElementError($this -> elements[$element],_("Champ obligatoire"));
          $retval=false;
        }
      }

      foreach($values as $value) {
        if (empty($value)) {
          continue;
        }
        if (!is_array($this -> _rules[$element]))
          continue;
        $GLOBALS['LSsession'] -> loadLSclass('LSformRule');
        foreach($this -> _rules[$element] as $rule) {
          $ruleType="LSformRule_".$rule['name'];
          $GLOBALS['LSsession'] -> loadLSclass($ruleType);
          if (! call_user_func(array( $ruleType,'validate') , $value, $rule['options'])) {
            $retval=false;
            $this -> setElementError($this -> elements[$element],$rule['options']['msg']);
          }
        }
      }
    }
    return $retval;
  }

  /**
   * V�rifie si au moins une valeur est pr�sente dans le tableau
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $data array tableau de valeurs
   *
   * @retval boolean true si au moins une valeur est pr�sente, false sinon
   */
  function checkRequired($data) {
    foreach($data as $val) {
      if (!empty($val))
        return true;
    }
    return;
  }

  /**
   * Verifie si la saisie du formulaire est pr�sente en POST
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la saisie du formulaire est pr�sente en POST, false sinon
   */
  function isSubmit() {
    if( (isset($_POST['validate']) && ($_POST['validate']=='LSform')) && (isset($_POST['idForm']) && ($_POST['idForm'] == $this -> idForm)) )
      return true;
    return;
  }

  /**
   * R�cup�re les valeurs post�es dans le formulaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si les valeurs ont bien �t� r�cup�r�es, false sinon.
   */
  function getPostData() {
    foreach($this -> elements as $element_name => $element) {
      if( !($element -> getPostData($this -> _postData)) ) {
        $GLOBALS['LSerror'] -> addErrorCode(202,$element_name);
        return;
      }
    }
    return true;
  }

  /*
   * Ajoute un �l�ment au formulaire
   * 
   * Ajoute un �l�ment au formulaire et d�finis les informations le concernant.
   *
   * @param[in] $type string Le type de l'�l�ment
   * @param[in] $name string Le nom de l'�l�ment
   * @param[in] $label string Le label de l'�l�ment
   * @param[in] $param mixed Param�tres suppl�mentaires
   *
   * @retval LSformElement
   */
  function addElement($type,$name,$label,$params=array()) {
    $elementType='LSformElement_'.$type;
    $GLOBALS['LSsession'] -> loadLSclass($elementType);
    if (!class_exists($elementType)) {
      $GLOBALS['LSerror'] -> addErrorCode(205,array('type' => $type));  
      return;
    }
    $element=$this -> elements[$name] = new $elementType($this,$name,$label,$params);
    if ($element) {
      return $element;
    }
    else {
      unset ($this -> elements[$name]);
      $GLOBALS['LSerror'] -> addErrorCode(206,array('element' => $name));
      return;
    }
  }

  /*
   * Ajoute une r�gle sur un �l�ment du formulaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $element string Le nom de l'�l�ment consern�
   * @param[in] $rule string Le nom de la r�gle � ajouter
   * @param[in] $options array Options (facultative)
   *
   * @retval boolean
   */
  function addRule($element, $rule, $options=array()) {
    if ( isset($this ->elements[$element]) ) {
      if ($this -> isRuleRegistered($rule)) {
        $this -> _rules[$element][]=array(
                  'name' => $rule,
                  'options' => $options
                  );
        return true;
      }
      else {
        $GLOBALS['LSerror'] -> addErrorCode(43,array('attr' => $element,'rule'=>$rule));      
        return;
      }
    }
    else {  
      $GLOBALS['LSerror'] -> addErrorCode(204,array('element' => $element));
      return;
    }
  }




  /*
   * D�finis comme requis un �l�ment
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $element string Le nom de l'�l�ment consern�
   *
   * @retval boolean
   */
  function setRequired($element) {
    if (isset( $this -> elements[$element] ) )
      return $this -> elements[$element] -> setRequired();
    else
      return;
  }

  /*
   * D�termine la valider de la r�gle
   *
   * Devra d�terminer si la r�gle passez en param�tre est correcte
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $element string Le nom de l'�l�ment consern�
   */
  function isRuleRegistered($rule) {
    $GLOBALS['LSsession'] -> loadLSclass('LSformRule');
    $GLOBALS['LSsession'] -> loadLSclass('LSformRule_'.$rule);
    return class_exists('LSformRule_'.$rule);
  }

  /**
   * Retourne les valeurs valid�s du formulaire
   *
   * @retval mixed Les valeurs valid�s du formulaire, ou false si elles ne le sont pas
   */
  function exportValues() {
    if ($this -> _isValidate) {
      return $this -> _postData;
    }
    else {
      return;
    }
  }

  /**
   * Retourn un �lement du formulaire
   *
   * @param[in] string $element Nom de l'�lement voulu
   *
   * @retval LSformElement L'�lement du formulaire voulu
   */
  function getElement($element) {
    return $this -> elements[$element];
  }

  /**
   * D�fini les valeurs des �lements � partir des valeurs post�es
   *
   * @retval boolean True si les valeurs ont �t� d�finies, false sinon.
   */
  function setValuesFromPostData() {
    if (empty($this -> _postData)) {
      return;
    }
    foreach($this -> _postData as $element => $values) {
      $this -> elements[$element] -> setValue($values);
    }
    return true;
  }

  /**
   * Retourne le code HTML d'un champ vide.
   * 
   * @param[in] string Le nom du champ du formulaire
   *
   * @retval string Le code HTML du champ vide.
   */
  function getEmptyField($element) {
    $element = $this -> getElement($element);
    if ($element) {      
      return $element -> getEmptyField();     
    }
    else {
      return;
    }
  }

}

?>
