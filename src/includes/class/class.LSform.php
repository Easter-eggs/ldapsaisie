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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * Formulaire pour LdapSaisie
 *
 * Cette classe gère les formulaires
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSform extends LSlog_staticLoggerClass {
  var $ldapObject;
  var $idForm;
  var $config;
  var $can_validate = true;
  var $elements = array();
  var $_rules = array();

  var $_postData = array();

  var $_elementsErrors = array();
  var $_isValidate = false;

  var $_notUpdate = array();

  var $maxFileSize = NULL;

  var $dataEntryForm = NULL;
  var $dataEntryFormConfig = NULL;

  var $warnings = array();

  var $api_mode = false;

  private $submited = false;

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
  public function __construct(&$ldapObject, $idForm, $submit=NULL, $api_mode=false){
    $this -> idForm = $idForm;
    if (!$submit) {
      $this -> submit = _("Validate");
    }
    else {
      $this -> submit = $submit;
    }
    $this -> api_mode = $api_mode;
    $this -> ldapObject =& $ldapObject;
    $this -> config = $ldapObject -> getConfig('LSform');
    LSsession :: loadLSclass('LSformElement');
  }

  /**
   * Return a configuration parameter (or default value)
   *
   * @param[] $param	The configuration parameter
   * @param[] $default	The default value (default : null)
   * @param[] $cast	Cast resulting value in specific type (default : disabled)
   *
   * @retval mixed The configuration parameter value or default value if not set
   **/
  public function getConfig($param, $default=null, $cast=null) {
    return LSconfig :: get($param, $default, $cast, $this -> config);
  }

  /**
   * Allow conversion of LSform to string
   *
   * @retval string The string representation of the LSform
   */
  public function __toString() {
    return "<LSform ".$this -> idForm." on ".$this -> ldapObject -> toString(false).">";
  }

  /**
   * Display the form
   *
   * @param[in] $LSform_action string|null The form action attribute value (optional, default: $_SERVER['PHP_SELF'])
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */
  public function display($LSform_action=null){
    // Load view dependencies
    self :: loadDependenciesDisplayView();

    // Load form dependencies
    LStemplate :: addJSscript('LSformElement_field.js');
    LStemplate :: addJSscript('LSformElement.js');

    LStemplate :: addHelpInfo(
      'LSform',
      array(
        'addFieldBtn' => _('Add a field to add another values.'),
        'removeFieldBtn' => _('Delete this field.')
      )
    );

    LStemplate :: assign('LSform_action', ($LSform_action?$LSform_action:$_SERVER['PHP_SELF']));
    $LSform_header = "\t<input type='hidden' name='validate' value='LSform'/>\n
    \t<input type='hidden' name='idForm' id='LSform_idform' value='".$this -> idForm."'/>\n
    \t<input type='hidden' name='LSform_objecttype' id='LSform_objecttype'  value='".$this -> ldapObject -> getType()."'/>\n
    \t<input type='hidden' name='LSform_objectdn' id='LSform_objectdn'  value='".$this -> ldapObject -> getValue('dn')."'/>\n";


    $LSform_object = array(
      'type' => $this -> ldapObject -> getType(),
      'dn' => $this -> ldapObject -> getValue('dn')
    );
    LStemplate :: assign('LSform_object',$LSform_object);

    $layout_config = $this -> getConfig("layout");

    if (!isset($this -> dataEntryFormConfig['disabledLayout']) || $this -> dataEntryFormConfig['disabledLayout']==false) {
      if (is_array($layout_config)) {
        LStemplate :: assign('LSform_layout',$layout_config);
      }
    }

    $fields = array();
    if (!isset($this -> dataEntryFormConfig['displayedElements']) && !is_array($this -> dataEntryFormConfig['displayedElements'])) {
      foreach($this -> elements as $element) {
        $field = array();
        $field = $element -> getDisplay();
        if (isset($this -> _elementsErrors[$element -> name])) {
          $field['errors']= $this -> _elementsErrors[$element -> name];
        }
        $fields[$element -> name] = $field;
      }
    }
    else {
      foreach($this -> dataEntryFormConfig['displayedElements'] as $elementName) {
        if (!isset($this -> elements[$elementName])) {
          LSerror :: addErrorCode('LSform_09',$elementName);
          continue;
        }
        $element = $this -> elements[$elementName];
        if ((isset($this -> dataEntryFormConfig['requiredAllAttributes']) && $this -> dataEntryFormConfig['requiredAllAttributes']) || isset($this -> dataEntryFormConfig['requiredAttributes']) && is_array($this -> dataEntryFormConfig['requiredAttributes']) && in_array($elementName,$this -> dataEntryFormConfig['requiredAttributes'])) {
            $element -> setRequired();
        }
        $field = array();
        $field = $element -> getDisplay();
        if (isset($this -> _elementsErrors[$element -> name])) {
          $field['errors']= $this -> _elementsErrors[$element -> name];
        }
        $fields[$element -> name] = $field;
      }
      // Add warning for other elements errors
      foreach(array_keys($this -> elements) as $name) {
        if (isset($this -> _elementsErrors[$name]) && !isset($fields[$name])) {
          foreach ($this -> _elementsErrors[$name] as $error) {
            $this -> addWarning("$name : $error");
          }
        }
      }
      $LSform_header .= "\t<input type='hidden' name='LSform_dataEntryForm' value='".$this -> dataEntryForm."'/>\n";
    }

    if ($this -> maxFileSize) {
      $LSform_header.="\t<input type='hidden' name='MAX_FILE_SIZE' value='".$this -> maxFileSize."'/>\n";
    }
    LStemplate :: assign('LSform_header',$LSform_header);

    LStemplate :: assign('LSform_fields',$fields);

    $JSconfig = array (
      'ajaxSubmit' => intval($this -> getConfig('ajaxSubmit', true, 'boolean')),
    );

    if (!empty($this -> warnings)) {
      $JSconfig['warnings']=$this -> warnings;
    }

    LStemplate :: addJSconfigParam('LSform_'.$this -> idForm,$JSconfig);
    LStemplate :: assign('LSform_submittxt',$this -> submit);
  }

 /*
  * Méthode chargeant les dépendances d'affichage d'une LSview
  *
  * @retval void
  */
  public static function loadDependenciesDisplayView($ldapObject=false, $search_view=false) {
    LStemplate :: addCssFile('LSform.css');
    LStemplate :: addJSscript('LSform.js');
    $customActionLabels = array ();
    if (is_a($ldapObject,'LSldapObject')) {
      $objectname=($search_view?$ldapObject -> getLabel():$ldapObject -> getDisplayName());
      $customActionsConfig = LSconfig :: get('LSobjects.'.$ldapObject->type_name.($search_view?'.LSsearch':'').'.customActions');
      if (is_array($customActionsConfig)) {
        foreach($customActionsConfig as $name => $config) {
          if (isset($config['question_format'])) {
            $customActionLabels['custom_action_'.$name.'_confirm_text'] = getFData(__($config['question_format']), $objectname);
          }
          elseif ($search_view) {
            $customActionLabels['custom_action_'.$name.'_confirm_text'] = getFData(
              _('Do you really want to execute custom action %{title} on this search ?'),
              $name
            );
          }
          else {
            $customActionLabels['custom_action_'.$name.'_confirm_text'] = getFData(
              _('Do you really want to execute custom action %{customAction} on %{objectname} ?'),
              array(
                      'objectname' => $objectname,
                      'customAction' => $name
              )
            );
          }
        }
      }
    }
    LStemplate :: addJSconfigParam('LSview_labels', array_merge(array(
      'delete_confirm_text'     => _('Do you really want to delete "%{name}"?'),
      'delete_confirm_title'    => _("Caution"),
      'delete_confirm_validate'  => _("Delete")
    ),$customActionLabels));
    if (LSsession :: loadLSclass('LSconfirmBox')) {
      LSconfirmBox :: loadDependenciesDisplay();
    }
    LStemplate :: addJSscript('LSview.js');
  }

  /**
   * Affiche la vue
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval void
   */
  public function displayView(){
    self :: loadDependenciesDisplayView($this -> ldapObject);

    $LSform_object = array(
      'type' => $this -> ldapObject -> getType(),
      'dn' => $this -> ldapObject -> getDn()
    );
    LStemplate :: assign('LSform_object',$LSform_object);
    $fields = array();
    foreach($this -> elements as $element) {
      $field = $element -> getDisplay();
      $fields[$element -> name] = $field;
    }
    LStemplate :: assign('LSform_fields',$fields);

    $layout_config = $this -> getConfig("layout");
    if (is_array($layout_config)) {
      LStemplate :: assign('LSform_layout',$layout_config);
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
  public function setElementError($attr,$msg=NULL) {
    if($msg!='') {
      $msg_error=getFData($msg,$attr->getLabel());
    }
    else {
      $msg_error=getFData(_("%{label} attribute data is not valid."),$attr->getLabel());
    }
    $this -> _elementsErrors[$attr->name][]=$msg_error;
    $this -> can_validate=false;
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
  public function definedError($element=NULL) {
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
  public function getErrors() {
    return $this -> _elementsErrors;
  }

  /**
   * Check form is submited and its data are validat
   *
   * @param[in] $onlyIfPresent boolean If true and data of this element is not present in POST data,
   *                                   just ignore it.
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true if form is submited and its data are valid, false otherwise
   */
  public function validate($onlyIfPresent=false){
    if(!$this -> can_validate)
      return;
    if ($this -> isSubmit()) {
      if (!$this -> getPostData($onlyIfPresent)) {
        LSerror :: addErrorCode('LSform_01');
        return;
      }
      // Check getPostData do not trigger fields errors
      if(!$this -> can_validate)
        return;
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
  public function checkData() {
    $retval=true;
    foreach ($this -> _postData as $element => $values) {
      if ($this -> definedError($element)) {
        $retval=false;
      }
      if(!is_array($values)) {
        $values=array($values);
      }
      if ($this -> elements[$element] -> isRequired()) {
        if (!$this -> checkRequired($values)) {
          $this -> setElementError($this -> elements[$element],_("Mandatory field"));
          $retval=false;
        }
      }

      // If no rule configured for this attribute, just ignore this check
      if (!isset($this -> _rules[$element]) || !is_array($this -> _rules[$element]))
        continue;

      // Load LSformRule class
      LSsession :: loadLSclass('LSformRule', null, true);

      // Iter on rules and check element values with each of them
      foreach($this -> _rules[$element] as $rule) {
        if (
          !LSformRule :: validate_values(
            $rule['name'], $values, $rule['options'], $this -> elements[$element]
          )
        ) {
          $retval = false;
          $this -> setElementError($this -> elements[$element], $rule['options']['msg']);
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
  public function checkRequired($data) {
    foreach($data as $val) {
      if (!is_empty($val))
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
  public function isSubmit() {
    if ($this -> submited)
      return true;
    if( (isset($_POST['validate']) && ($_POST['validate']=='LSform')) && (isset($_POST['idForm']) && ($_POST['idForm'] == $this -> idForm)) )
      return true;
    return;
  }

  /**
   * Set form as submited
   *
   * @retval void
   */
  public function setSubmited() {
    $this -> submited = true;
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
  public function setPostData($data,$consideredAsSubmit=false) {
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
   * Retreive POST data of the form
   *
   * @param[in] $onlyIfPresent boolean If true and data of this element is not present in POST data,
   *                                   just ignore it.
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval boolean true if POST data are retreived, false otherwise
   */
  public function getPostData($onlyIfPresent=false) {
    if (is_null($this -> dataEntryForm)) {
      foreach($this -> elements as $element_name => $element) {
        if( !($element -> getPostData($this -> _postData, $onlyIfPresent)) ) {
          LSerror :: addErrorCode('LSform_02',$element_name);
          return;
        }
      }
    }
    else {
      $elementsList = $this -> dataEntryFormConfig['displayedElements'];
      if (isset($this -> dataEntryFormConfig['defaultValues']) && is_array($this -> dataEntryFormConfig['defaultValues'])) {
        $this -> setPostData($this -> dataEntryFormConfig['defaultValues']);
        $elementsList = array_merge($elementsList,array_keys($this -> dataEntryFormConfig['defaultValues']));
      }

      foreach($elementsList as $elementName) {
        if (!isset($this -> elements[$elementName])) {
          LSerror :: addErrorCode('LSform_09',$elementName);
          continue;
        }
        $element = $this -> elements[$elementName];
        if ((isset($this -> dataEntryFormConfig['requiredAllAttributes']) && $this -> dataEntryFormConfig['requiredAllAttributes']) || isset($this -> dataEntryFormConfig['requiredAttributes']) && is_array($this -> dataEntryFormConfig['requiredAttributes']) && in_array($elementName,$this -> dataEntryFormConfig['requiredAttributes'])) {
            $element -> setRequired();
        }
        if( !($element -> getPostData($this -> _postData, $onlyIfPresent)) ) {
          LSerror :: addErrorCode('LSform_02',$element_name);
          return;
        }
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
  public function addElement($type,$name,$label,$params=array(),&$attr_html) {
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
   * Check if form has a specified element (by attr name)
   *
   * @param[in] $attr string The element/attribute name
   *
   * @retval boolean
   **/
  public function hasElement($name) {
    return isset($this -> elements[$name]);
  }

  /**
   * Check if a specified element (by attr name) is freezed
   *
   * @param[in] $attr string The element/attribute name
   *
   * @retval boolean
   **/
  public function isFreeze($name) {
    return isset($this -> elements[$name]) && $this -> elements[$name] -> isFreeze($name);
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
  public function addRule($element, $rule, $options=array()) {
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
  public function setRequired($element) {
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
  public function isRuleRegistered($rule) {
    LSsession :: loadLSclass('LSformRule');
    LSsession :: loadLSclass('LSformRule_'.$rule);
    return class_exists('LSformRule_'.$rule);
  }

  /**
   * Retourne les valeurs validés du formulaire
   *
   * @retval mixed Les valeurs validés du formulaire, ou false si elles ne le sont pas
   */
  public function exportValues() {
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
  public function getElement($element) {
    return $this -> elements[$element];
  }

  /**
   * Return the values of an element
   *
   * If form is posted, retreive values from postData, otherwise
   * retreive value from the element.
   *
   * @param[in] string $element The element name
   *
   * @retval mixed The element values
   **/
  public function getValue($element) {
    if ($this -> isSubmit() && $this -> _postData) {
      return $this -> _postData[$element];
    }
    return $this -> elements[$element] -> getValue();
  }

  /**
   * Défini les valeurs des élements à partir des valeurs postées
   *
   * @retval boolean True si les valeurs ont été définies, false sinon.
   */
  public function setValuesFromPostData() {
    if (empty($this -> _postData)) {
      return;
    }
    foreach($this -> _postData as $element => $values) {
      $this -> elements[$element] -> setValueFromPostData($values);
    }
    return true;
  }

  /**
   * Return the HTML code of an empty form field
   *
   * @param[in] $element string The form element name
   * @param[in] $value_idx integer|null The value index (optional, default: null == 0)
   *
   * @retval string|null The HTML code of the specified field if exist, null otherwise
   */
  public function getEmptyField($element, $value_idx=null) {
    $element = $this -> getElement($element);
    if ($element) {
      return $element -> getEmptyField($value_idx);
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
  public function setMaxFileSize($size) {
    $this -> maxFileSize = $size;
  }

  /**
   * Applique un masque de saisie au formulaire
   *
   * @param[in] $dataEntryForm string Le nom du masque de saisie
   *
   * @retval boolean True si le masque de saisie a été appliqué, False sinon
   **/
   public function applyDataEntryForm($dataEntryForm) {
     $dataEntryForm=(string)$dataEntryForm;
     $objType = $this -> ldapObject -> getType();
     $config = $this -> getConfig("dataEntryForm.$dataEntryForm");
     if (is_array($config)) {
       if (!is_array($config['displayedElements'])) {
         LSerror :: addErrorCode('LSform_08',$dataEntryForm);
       }
       $this -> dataEntryForm       = $dataEntryForm;
       $this -> dataEntryFormConfig = $config;

       // Set default value of displayed elements
       if(is_array($config['defaultValues'])) {
         foreach($config['displayedElements'] as $el) {
           if (isset($config['defaultValues'][$el])) {
             if (isset($this -> elements[$el])) {
               $this -> elements[$el] -> setValueFromPostData($config['defaultValues'][$el]);
             }
           }
         }
       }
       return true;
     }
     LSerror :: addErrorCode('LSform_07',$dataEntryForm);
     return;
   }

   /**
    * Liste les dataEntryForm disponible pour un type d'LSldapObject
    *
    * @param[in] $type string Le type d'LSldapObject
    *
    * @retval array Tableau contenant la liste de dataEntryForm disponible pour ce type d'LSldapObject (nom => label)
    **/
    public static function listAvailableDataEntryForm($type) {
      $retval=array();
      if (LSsession ::loadLSobject($type)) {
        // Static method: couldn't use $this -> getConfig()
        $config=LSconfig :: get("LSobjects.".$type.".LSform.dataEntryForm");
        if (is_array($config)) {
          foreach($config as $name => $conf) {
            if (isset($conf['label'])) {
              $retval[$name]=__($conf['label']);
            }
            else {
              $retval[$name]=__($name);
            }
          }
        }
      }
      return $retval;
    }

   /**
    * Ajoute un avertissement au sujet du formulaire
    *
    * @param[in] $txt string Le texte de l'avertissement
    *
    * @retval void
    **/
   public function addWarning($txt) {
     $this -> warnings[]=$txt;
   }

  /**
   * Méthode Ajax permetant de retourner le code HTML d'un élément du formulaire vide
   *
   * @param[in] &$data Variable de retour
   *
   * @retval void
   **/
  public static function ajax_onAddFieldBtnClick(&$data) {
    if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['idform'])) && (isset($_REQUEST['fieldId'])) ) {
      if (LSsession ::loadLSobject($_REQUEST['objecttype'])) {
        $object = new $_REQUEST['objecttype']();
        $object -> loadData($_REQUEST['objectdn']);
        $form = $object -> getForm($_REQUEST['idform']);
        $value_idx = (isset($_REQUEST['value_idx'])?$_REQUEST['value_idx']:0);
        $emptyField = $form -> getEmptyField($_REQUEST['attribute'], $value_idx);
        if ( $emptyField ) {
          $data = array(
            'html' => $emptyField,
            'value_idx' => $value_idx,
            'fieldId' => $_REQUEST['fieldId'],
            'fieldtype' => get_class($form -> getElement($_REQUEST['attribute']))
          );
        }
      }
    }
  }

  /**
   * CLI autocompleter for form attributes values
   *
   * @param[in] &$opts      array                 Reference of array of avalaible autocomplete options
   * @param[in] $comp_word  string                The command word to autocomplete
   * @param[in] $multiple_value_delimiter string  The multiple value delimiter (optional, default: "|")
   *
   * @retval void
   */
  public function autocomplete_attrs_values(&$opts, $comp_word, $multiple_value_delimiter='|') {
    if ($comp_word && strpos($comp_word, '=') !== false) {
      // Check if $comp_word is quoted
      $quote_char = LScli :: unquote_word($comp_word);

      // Attribute name already entered: check it and autocomplete using LSformElement -> autocomplete_opts()
      $comp_word_parts = explode('=', $comp_word);
      $attr_name = trim($comp_word_parts[0]);
      $attr_value = (count($comp_word_parts) > 1?implode('=', array_slice($comp_word_parts, 1)):'');
      if (!$this -> hasElement($attr_name)) {
        self :: log_error("Attribute '$attr_name' does not exist or not present in modify form.");
        return;
      }
      $this -> elements[$attr_name] -> autocomplete_attr_values($opts, $comp_word, $attr_value, $multiple_value_delimiter, $quote_char);
    }
    else {
      // Attribute name not already entered: add attribute name options
      // Check if $comp_word is quoted and retreived quote char
      if ($comp_word) {
        $quote_char = LScli :: unquote_word($comp_word);
      }
      else
        $quote_char = '';
      foreach (array_keys($this -> elements) as $attr_name) {
        $opts[] = LScli :: quote_word("$attr_name=", $quote_char);
      }
    }
  }

}

/**
 * Error Codes
 */
LSerror :: defineError('LSform_01',
___("LSform : Error during the recovery of the values of the form.")
);
LSerror :: defineError('LSform_02',
___("LSform : Error durring the recovery of the value of the field '%{element}'.")
);
// No longer used
/*LSerror :: defineError(203,
___("LSform : Data of the field %{element} are not validate.")
);*/
LSerror :: defineError('LSform_04',
___("LSform : The field %{element} doesn't exist.")
);
LSerror :: defineError('LSform_05',
___("LSfom : Field type unknow (%{type}).")
);
LSerror :: defineError('LSform_06',
___("LSform : Error during the creation of the element '%{element}'.")
);
LSerror :: defineError('LSform_07',
___("LSform : The data entry form %{name} doesn't exist.")
);
LSerror :: defineError('LSform_08',
___("LSform : The data entry form %{name} is not correctly configured.")
);
LSerror :: defineError('LSform_09',
___("LSform : The element %{name}, listed as displayed in data entry form configuration, doesn't exist.")
);
