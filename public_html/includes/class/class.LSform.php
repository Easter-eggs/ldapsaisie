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
 * Cette classe gère les formulaires
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
  
  var $maxFileSize = NULL;

  /**
   * Constructeur
   *
   * Cette methode construit l'objet et définis la configuration.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $idForm [<b>required</b>] string L'identifiant du formulaire
   * @param[in] $submit string La valeur du bouton submit
   *
   * @retval void
   */ 
  function LSform (&$ldapObject,$idForm,$submit=NULL){
    $this -> idForm = $idForm;
    if (!$submit) {
      $this -> submit = _("Validate");
    }
    else {
      $this -> submit = $submit;
    }
    $this -> ldapObject = $ldapObject;
    LSsession :: loadLSclass('LSformElement');
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
      self :: loadDependenciesDisplayView();
    }
    else {
      LSsession :: addJSscript('LSformElement_field.js');
      LSsession :: addJSscript('LSformElement.js');
      LSsession :: addJSscript('LSform.js');
      LSsession :: addCssFile('LSform.css');
    }
    
    LSsession :: addHelpInfos(
      'LSform',
      array(
        'addFieldBtn' => _('Add a field to add another values.'),
        'removeFieldBtn' => _('Delete this field.')
      )
    );
    
    $GLOBALS['Smarty'] -> assign('LSform_action',$_SERVER['PHP_SELF']);
    $LSform_header = "\t<input type='hidden' name='validate' value='LSform'/>\n
    \t<input type='hidden' name='idForm' id='LSform_idform' value='".$this -> idForm."'/>\n
    \t<input type='hidden' name='LSform_objecttype' id='LSform_objecttype'  value='".$this -> ldapObject -> getType()."'/>\n
    \t<input type='hidden' name='LSform_objectdn' id='LSform_objectdn'  value='".$this -> ldapObject -> getValue('dn')."'/>\n";

    
    $LSform_object = array(
      'type' => $this -> ldapObject -> getType(),
      'dn' => $this -> ldapObject -> getValue('dn')
    );
    $GLOBALS['Smarty'] -> assign('LSform_object',$LSform_object);
    
    $layout_config=LSconfig :: get("LSobjects.".$LSform_object['type'].".LSform.layout");
    if (is_array($layout_config)) {
      $GLOBALS['Smarty'] -> assign('LSform_layout',$layout_config);
      $GLOBALS['Smarty'] -> assign('LSform_layout_nofield_label',_('No field.'));
    }
    
    $fields = array();
    foreach($this -> elements as $element) {
      $field = array();
      $field = $element -> getDisplay();
      if (isset($this -> _elementsErrors[$element -> name])) {
        $field['errors']= $this -> _elementsErrors[$element -> name];
      }
      $fields[$element -> name] = $field;
    }
    
    if ($this -> maxFileSize) {
      $LSform_header.="\t<input type='hidden' name='MAX_FILE_SIZE' value='".$this -> maxFileSize."'/>\n";
    }
    $GLOBALS['Smarty'] -> assign('LSform_header',$LSform_header);
    
    $GLOBALS['Smarty'] -> assign('LSform_fields',$fields);
    if($this -> can_validate) {
      $GLOBALS['Smarty'] -> assign('LSform_submittxt',$this -> submit);
    }
  }
  
 /*
  * Méthode chargeant les dépendances d'affichage d'une LSview
  * 
  * @retval void
  */
  public static function loadDependenciesDisplayView() {
    LSsession :: addCssFile('LSform.css');
    LSsession :: addJSscript('LSform.js');
    LSsession :: addJSconfigParam('LSview_labels', array(
      'delete_confirm_text'     => _("Do you really want to delete"),
      'delete_confirm_title'    => _("Caution"),
      'delete_confirm_validate'  => _("Delete")
    ));
    if (LSsession :: loadLSclass('LSconfirmBox')) {
      LSconfirmBox :: loadDependenciesDisplay();
    }
    LSsession :: addJSscript('LSview.js');
  }
  
  /**
   * Affiche la vue
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */ 
  function displayView(){
    self :: loadDependenciesDisplayView();
    
    $LSform_object = array(
      'type' => $this -> ldapObject -> getType(),
      'dn' => $this -> ldapObject -> getDn()
    );
    $GLOBALS['Smarty'] -> assign('LSform_object',$LSform_object);
    $fields = array();
    foreach($this -> elements as $element) {
      $field = $element -> getDisplay();
      $fields[$element -> name] = $field;
    }
    $GLOBALS['Smarty'] -> assign('LSform_fields',$fields);
    
    $layout_config=LSconfig :: get("LSobjects.".$LSform_object['type'].".LSform.layout");
    if (is_array($layout_config)) {
      $GLOBALS['Smarty'] -> assign('LSform_layout',$layout_config);
      $GLOBALS['Smarty'] -> assign('LSform_layout_nofield_label',_('No field.'));
    }
  }  
  
  /**
   * Défini l'erreur sur un champ
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $attr [<b>required</b>] string Le nom du champ
   * @param[in] $msg Le format du message d'erreur à afficher (pouvant comporter
   *                 des valeurs %{[n'importe quoi]} qui seront remplacé par le label
   *                 du champs concerné.
   *
   * @retval void
   */ 
  function setElementError($attr,$msg=NULL) {
    if($msg!='') {
      $msg_error=getFData($msg,$attr->getLabel());
    }
    else {
      $msg_error=getFData(_("%{label} attribute data is not valid."),$attr->getLabel());
    }
    $this -> _elementsErrors[$attr->name][]=$msg_error;
  }
  
  /**
   * Savoir si des erreurs son définie pour un élement du formulaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $element [<b>required</b>] string Le nom de l'élement
   * 
   * @retval boolean
   */ 
  function definedError($element=NULL) {
    if ($element) {
      return isset($this -> _elementsErrors[$element]);
    }
    else {
      return !empty($this -> _elementsErrors);
    }
  }
  
  /**
   * Retourne le tableau des erreurs
   * 
   * @retval Array array(element => array(errors))
   */
  function getErrors() {
    return $this -> _elementsErrors;
  }
  
  /**
   * Verifie si le formulaire a été validé et que les données sont valides.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si le formulaire a été validé et que les données ont été validées, false sinon
   */ 
  function validate(){
    if(!$this -> can_validate)
      return;
    if ($this -> isSubmit()) {
      if (!$this -> getPostData()) {
        LSerror :: addErrorCode('LSform_01');
        return;
      }
      $this -> setValuesFromPostData();
      //Validation des données ici !!! ///
      if (!$this -> checkData()) {
        return;
      }
      LSdebug("Data are checked up");
      $this -> _isValidate = true;
      return true;
    }
    return false;
  }

  /**
   * Vérifier les données du formulaire à partir des régles définis sur les champs
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
          $this -> setElementError($this -> elements[$element],_("Mandatory field"));
          $retval=false;
        }
      }

      foreach($values as $value) {
        if (empty($value)) {
          continue;
        }
        if (!is_array($this -> _rules[$element]))
          continue;
        LSsession :: loadLSclass('LSformRule');
        foreach($this -> _rules[$element] as $rule) {
          $ruleType="LSformRule_".$rule['name'];
          LSsession :: loadLSclass($ruleType);
          if (! call_user_func(array( $ruleType,'validate') , $value, $rule['options'], $this -> getElement($element))) {
            $retval=false;
            $this -> setElementError($this -> elements[$element],$rule['options']['msg']);
          }
        }
      }
    }
    return $retval;
  }

  /**
   * Vérifie si au moins une valeur est présente dans le tableau
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $data array tableau de valeurs
   *
   * @retval boolean true si au moins une valeur est présente, false sinon
   */
  function checkRequired($data) {
    foreach($data as $val) {
      if (!empty($val)||(is_string($val)&&($val=="0")))
        return true;
    }
    return;
  }

  /**
   * Verifie si la saisie du formulaire est présente en POST
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si la saisie du formulaire est présente en POST, false sinon
   */
  function isSubmit() {
    if( (isset($_POST['validate']) && ($_POST['validate']=='LSform')) && (isset($_POST['idForm']) && ($_POST['idForm'] == $this -> idForm)) )
      return true;
    return;
  }

  /**
   * Défini arbitrairement des données en POST
   * 
   * @author Benjamin Renard <brenard@easter-eggs.com>
   * 
   * @param[in] $data array('attr' => array(values)) Tableau des données du formulaire
   * @param[in] $consideredAsSubmit Définie si on force le formualaire comme envoyer
   * 
   * @retval boolean true si les données ont été définies, false sinon
   */
  function setPostData($data,$consideredAsSubmit=false) {
    if (is_array($data)) {
      foreach($data as $key => $values) {
        if (!is_array($values)) {
          $values = array($values);
        }
        $_POST[$key] = $values;
      }
      
      if ($consideredAsSubmit) {
        $_POST['validate']='LSform';
        $_POST['idForm']=$this -> idForm;
      }
      
      return true;
    }
    return;
  }

  /**
   * Récupère les valeurs postées dans le formulaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true si les valeurs ont bien été récupérées, false sinon.
   */
  function getPostData() {
    foreach($this -> elements as $element_name => $element) {
      if( !($element -> getPostData($this -> _postData)) ) {
        LSerror :: addErrorCode('LSform_02',$element_name);
        return;
      }
    }
    return true;
  }

  /**
   * Ajoute un élément au formulaire
   * 
   * Ajoute un élément au formulaire et définis les informations le concernant.
   *
   * @param[in] $type string Le type de l'élément
   * @param[in] $name string Le nom de l'élément
   * @param[in] $label string Le label de l'élément
   * @param[in] $param mixed Paramètres supplémentaires
   *
   * @retval LSformElement
   */
  function addElement($type,$name,$label,$params=array(),&$attr_html) {
    $elementType='LSformElement_'.$type;
    LSsession :: loadLSclass($elementType);
    if (!class_exists($elementType)) {
      LSerror :: addErrorCode('LSform_05',array('type' => $type));  
      return;
    }
    $element=$this -> elements[$name] = new $elementType($this,$name,$label,$params,$attr_html);
    if ($element) {
      return $element;
    }
    else {
      unset ($this -> elements[$name]);
      LSerror :: addErrorCode('LSform_06',array('element' => $name));
      return;
    }
  }

  /**
   * Ajoute une règle sur un élément du formulaire
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $element string Le nom de l'élément conserné
   * @param[in] $rule string Le nom de la règle à ajouter
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
        LSerror :: addErrorCode('LSattribute_03',array('attr' => $element,'rule'=>$rule));      
        return;
      }
    }
    else {  
      LSerror :: addErrorCode('LSform_04',array('element' => $element));
      return;
    }
  }




  /**
   * Définis comme requis un élément
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $element string Le nom de l'élément conserné
   *
   * @retval boolean
   */
  function setRequired($element) {
    if (isset( $this -> elements[$element] ) )
      return $this -> elements[$element] -> setRequired();
    else
      return;
  }

  /**
   * Détermine la valider de la règle
   *
   * Devra déterminer si la règle passez en paramètre est correcte
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @param[in] $element string Le nom de l'élément conserné
   */
  function isRuleRegistered($rule) {
    LSsession :: loadLSclass('LSformRule');
    LSsession :: loadLSclass('LSformRule_'.$rule);
    return class_exists('LSformRule_'.$rule);
  }

  /**
   * Retourne les valeurs validés du formulaire
   *
   * @retval mixed Les valeurs validés du formulaire, ou false si elles ne le sont pas
   */
  function exportValues() {
    if ($this -> _isValidate) {
      $retval=array();
      foreach($this -> _postData as $element => $values) {
        $retval[$element] = $this -> elements[$element] -> exportValues();
      }
      return $retval;
    }
    else {
      return;
    }
  }

  /**
   * Retourn un élement du formulaire
   *
   * @param[in] string $element Nom de l'élement voulu
   *
   * @retval LSformElement L'élement du formulaire voulu
   */
  function getElement($element) {
    return $this -> elements[$element];
  }

  /**
   * Défini les valeurs des élements à partir des valeurs postées
   *
   * @retval boolean True si les valeurs ont été définies, false sinon.
   */
  function setValuesFromPostData() {
    if (empty($this -> _postData)) {
      return;
    }
    foreach($this -> _postData as $element => $values) {
      $this -> elements[$element] -> setValueFromPostData($values);
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
  
  /**
   * Défini la taille maximal pour les fichiers envoyés par le formualaire
   * 
   * @param[in] $size La taille maximal en octets
   * 
   * @retval  void
   **/
  function setMaxFileSize($size) {
    $this -> maxFileSize = $size;
  }


  public static function ajax_onAddFieldBtnClick(&$data) {
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['idform'])) && (isset($_REQUEST['fieldId'])) ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $object = new $_REQUEST['objecttype']();
        $object -> loadData($_REQUEST['objectdn']);
        $form = $object -> getForm($_REQUEST['idform']);
        $emptyField=$form -> getEmptyField($_REQUEST['attribute']);
        if ( $emptyField ) {
          $data = array(
            'html' => $form -> getEmptyField($_REQUEST['attribute']),
            'fieldId' => $_REQUEST['fieldId'],
            'fieldtype' => get_class($form -> getElement($_REQUEST['attribute']))
          );
        }
      }
    }
  }
}

/**
 * Error Codes
 */
LSerror :: defineError('LSform_01',
_("LSform : Error during the recovery of the values of the form.")
);
LSerror :: defineError('LSform_02',
_("LSform : Error durring the recovery of the value of the field '%{element}'.")
);
// No longer used
/*LSerror :: defineError(203,
_("LSform : Data of the field %{element} are not validate.")
);*/
LSerror :: defineError('LSform_04',
_("LSform : The field %{element} doesn't exist.")
);
LSerror :: defineError('LSform_05',
_("LSfom : Field type unknow (%{type}).")
);
LSerror :: defineError('LSform_06',
_("LSform : Error during the creation of the element '%{element}'.")
);
// No longer used
/*LSerror :: defineError(207,
_("LSform : No value has been entered into the field '%{element}'.")
);*/

?>