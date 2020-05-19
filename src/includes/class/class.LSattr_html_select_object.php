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
 * Type d'attribut HTML select_object
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSattr_html_select_object extends LSattr_html{

  var $unrecognizedValues=false;

  /**
   * Ajoute l'attribut au formualaire passer en paramètre
   *
   * @param[in] &$form LSform Le formulaire
   * @param[in] $idForm L'identifiant du formulaire
   * @param[in] $data Valeur du champs du formulaire
   *
   * @retval LSformElement L'element du formulaire ajouté
   */
  public function addToForm (&$form,$idForm,$data=NULL) {
    $this -> config['attrObject'] = $this;
    $element=$form -> addElement('select_object', $this -> name, $this -> getLabel(), $this -> config, $this);
    if(!$element) {
      LSerror :: addErrorCode('LSform_06',$this -> name);
      return;
    }
    if ($data) {
      if (!is_array($data)) {
        $data=array($data);
      }
      $values=$this -> getFormValues($data);
      if ($values) {
        $element -> setValue($values);
      }
    }
    return $element;
  }

  /**
   * Effectue les tâches nécéssaires au moment du rafraichissement du formulaire
   *
   * Récupère un array du type array('DNs' => 'displayName') à partir d'une
   * liste de DNs.
   *
   * @param[in] $data mixed La valeur de l'attribut (liste de DNs)
   *
   * @retval mixed La valeur formatée de l'attribut (array('DNs' => 'displayName'))
   **/
  public function refreshForm($data,$fromDNs=false) {
    return $this -> getFormValues($data,$fromDNs);
  }

  /**
   * Check and return selectable objects configuration and eventually instanciate
   * it if &instanciate
   *
   * @param[in] boolean &$instanciated_objects reference If this reference point to an array, each valid
   *                                                     selectable object type will be instanciated in this
   *                                                     array with object type name as key.
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array|false Selectable objects configuration or False on error.
   */
  public function getSelectableObjectsConfig(&$instanciated_objects) {
    $confs = $this -> getConfig('html_options.selectable_object');
    if (!is_array($confs)) {
      self :: log_debug('getSelectableObjectsConfig(): html_options.selectable_object not properly configured');
      return false;
    }

    // Make sure we have an array or configured selectable objects
    if (isset($confs['object_type'])) {
      $confs = array($confs);
    }

    // Return confs
    $ret_confs = array();

    // For each configured object types:
    // - check we have the minimal configuration (object_type & value_attribute)
    // - check we to not have multiple conf for the same object type
    // - load object type
    // - implement an object of this type (if is_array($instanciated_objects))
    // - if value are from attributes, check this attribute exists
    foreach ($confs as $conf) {
      if (!isset($conf['object_type'])) {
        LSerror :: addErrorCode(
          'LSattr_html_select_object_01',
          array(
            'attr' => $this -> name,
            'parameter' => 'object_type',
          )
        );
        return false;
      }

      if (!isset($conf['value_attribute'])) {
        LSerror :: addErrorCode(
          'LSattr_html_select_object_01',
          array(
            'attr' => $this -> name,
            'parameter' => 'value_attribute',
          )
        );
        return false;
      }

      if (array_key_exists($conf['object_type'], $ret_confs)) {
        LSerror :: addErrorCode('LSattr_html_select_object_04', array('type' => $conf['object_type'], 'attr' => $this -> name));
        return false;
      }
      $object_type = $conf['object_type'];

      if (!LSsession :: loadLSobject($object_type))
        return false;

      if (is_array($instanciated_objects))
        $instanciated_objects[$object_type] = new $object_type();

      if (!($conf['value_attribute']=='dn') && !($conf['value_attribute']=='%{dn}')) {
        if (!$object_type :: hasAttr($conf['value_attribute'])) {
          LSerror :: addErrorCode(
            'LSattr_html_select_object_02',
            array(
              'attr' => $this -> name,
              'object_type' => $conf['object_type'],
              'value_attribute' => $conf['value_attribute'],
            )
          );
          return false;
        }
      }

      // Handle other parameters
      $conf['display_name_format'] = LSconfig :: get('display_name_format', null, null, $conf);
      $conf['onlyAccessible'] = LSconfig :: get('onlyAccessible', false, 'bool', $conf);
      $conf['filter'] = LSconfig :: get('filter', null, null, $conf);

      $ret_confs[$object_type] = $conf;
    }
    return $ret_confs;
  }


  /**
   * Return an array of selected objects with DN as key and as value, an array with object name (key name)
   * and type (key object_type).
   *
   * @param[in] mixed $values array|null Array of the input values ()
   * @param[in] boolean $fromDNs boolean If true, considered provided values as DNs (default: false)
   * @param[in] boolean $retreiveAttrValues boolean If true, attribute values will be returned instead
   *                                                of selected objects info (default: false)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array|false Array of selected objects with DN as key and object info as value or array
   *                     of attribute values if $retreiveAttrValues==true or False on error.
   */
  public function getFormValues($values=NULL, $fromDNs=false, $retreiveAttrValues=false) {
    self :: log_debug("getFormValues(): input values=".varDump($values));
    // Check parameters consistency
    if ($retreiveAttrValues && !$fromDNs) {
      self :: log_fatal('getFormValues(): $fromDNs must be true if $retreiveAttrValues==true');
      return false;
    }
    if (!is_array($values)) {
      self :: log_warning('getFormValues(): $values is not array');
      return false;
    }

    // Retreive/check selectable objects config
    $objs = array();
    $confs = $this -> getSelectableObjectsConfig($objs);
    if (!is_array($confs) || empty($confs)) {
      self :: log_warning('getFormValues(): invalid selectable objects config');
      return false;
    }

    $selected_objects = array();
    $unrecognizedValues = array();
    $found_values = array();
    foreach ($confs as $conf) {
      foreach($values as $value) {
        // If we already mark its value as unrecognized, pass
        if (in_array($value, $unrecognizedValues))
          continue;

        // Ignore empty value
        if (empty($value))
          continue;

        // Determine value attribute: DN/attribute valued (or force form DNs)
        if(($conf['value_attribute']=='dn') || ($conf['value_attribute']=='%{dn}') || $fromDNs) {
          // Construct resulting list from DN values
          if ($conf['onlyAccessible']) {
            if (!LSsession :: canAccess($conf['object_type'], $value)) {
              self :: log_debug("getFormValues(): ".$conf['object_type']."($value): not accessible, pass");
              continue;
            }
          }

          // Load object data (with custom filter if defined)
          if(!$objs[$conf['object_type']] -> loadData($value, $conf['filter'])) {
            self :: log_debug("getFormValues(): ".$conf['object_type']."($value): not found, pass");
            continue;
          }
          self :: log_debug("getFormValues(): ".$conf['object_type']."($value): found");

          // Check if it's the first this value match with an object
          if (isset($found_values[$value])) {
            // DN match with multiple object type
            LSerror :: addErrorCode('LSattr_html_select_object_03',array('val' => $value, 'attribute' => $this -> name));
            $unrecognizedValues[] = $value;
            unset($selected_objects[$found_values[$value]]);
            break;
          }
          $found_values[$value] = $value;

          if ($retreiveAttrValues) {
            // Retreive attribute value case: $selected_objects[dn] = attribute value
            if(($conf['value_attribute']=='dn') || ($conf['value_attribute']=='%{dn}')) {
              $selected_objects[$value] = $value;
            }
            else {
              $val = $objs[$conf['object_type']] -> getValue($conf['value_attribute']);
              if (!empty($val)) {
                $selected_objects[$value] = $val[0];
              }
              else {
                LSerror :: addErrorCode(
                  'LSattr_html_select_object_06',
                  array(
                    'name' => $objs[$conf['object_type']] -> getDisplayName($conf['display_name_format']),
                    'attr' => $this -> name
                  )
                );
              }
            }
          }
          else {
            // General case: $selected_objects[dn] = array(name + object_type)
            $selected_objects[$value] = array(
              'name' => $objs[$conf['object_type']] -> getDisplayName($conf['display_name_format']),
              'object_type' => $conf['object_type'],
            );
            self :: log_debug("getFormValues(): ".$conf['object_type']."($value): ".varDump($selected_objects[$value]));
          }
        }
        else {
          // Construct resulting list from attributes values
          $filter = Net_LDAP2_Filter::create($conf['value_attribute'], 'equals', $value);
          if (isset($conf['filter']))
            $filter = LSldap::combineFilters('and', array($filter, $conf['filter']));
          $sparams = array();
          $sparams['onlyAccessible'] = $conf['onlyAccessible'];
          $listobj = $objs[$conf['object_type']] -> listObjectsName(
            $filter,
            NULL,
            $sparams,
            $conf['display_name_format']
          );
          if (count($listobj)==1) {
            if (isset($found_values[$value])) {
                // Value match with multiple object type
                LSerror :: addErrorCode('LSattr_html_select_object_03',array('val' => $value, 'attribute' => $this -> name));
                $unrecognizedValues[] = $value;
                unset($selected_objects[$found_values[$value]]);
                break;
            }
            $dn = key($listobj);
            $selected_objects[$dn] = array(
              'name' => $listobj[$dn],
              'object_type' => $conf['object_type'],
            );
            $found_values[$value] = $dn;
          }
          else if(count($listobj) > 1) {
            LSerror :: addErrorCode('LSattr_html_select_object_03',array('val' => $value, 'attribute' => $this -> name));
            if (!in_array($value, $unrecognizedValues))
              $unrecognizedValues[] = $value;
            break;
          }
        }
      }
    }

    // Check if all values have been found (or already considered as unrecognized)
    foreach ($values as $value) {
      if (!isset($found_values[$value]) && !in_array($value, $unrecognizedValues)) {
        self :: log_debug("getFormValues(): value '$value' not recognized");
        $unrecognizedValues[] = $value;
      }
    }

    // Retreive attribute values case: return forged array values (list of attribute values)
    if ($retreiveAttrValues)
      return array_values($selected_objects);

    // General case
    self :: log_debug("getFormValues(): unrecognizedValues=".varDump($unrecognizedValues));
    $this -> unrecognizedValues = $unrecognizedValues;

    self :: log_debug("getFormValues(): final values=".varDump($selected_objects));
    return $selected_objects;
  }

  /**
   * Get LSselect ID for this attribute
   *
   * @return string The LSselect ID for this attribute
   */
  public function getLSselectId() {
    $id = "";
    if ($this -> attribute -> ldapObject -> isNew())
      $id .= $this -> attribute -> ldapObject -> getType();
    else
      $id .= $this -> attribute -> ldapObject -> getDn();
    $id .= "|".$this -> name;
    return $id;
  }


  /**
   * Return array of atttribute values form array of form values
   *
   * @param[in] mixed Array of form values
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array|false Array of attribute values or False on error.
   */
  public function getValuesFromFormValues($values=NULL) {
    if (is_array($values)) {
      return $this -> getFormValues(array_keys($values), true, true);
    }
    return false;
  }

  /**
   * Return an array of selected objects with DN as key and display name as value
   * from LSselect
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau associatif des objects selectionnés avec en clé
   *               le DN et en valeur ce qui sera affiché.
   */
  public function getValuesFromLSselect() {
    $LSselect_id = $this -> getLSselectId();
    if (LSsession :: loadLSclass('LSselect', null, true) && LSselect :: exists($LSselect_id)) {
      $selected_objects = LSselect :: getSelectedObjects($LSselect_id);
      self :: log_debug("getValuesFromLSselect(): selected objects retreived from LSselect '$LSselect_id' = ".varDump($selected_objects));
      if (is_array($selected_objects)) {
        return $this -> getFormValues(
          array_keys($selected_objects),
          true
        );
      }
    }
    return false;
  }

  /**
   * Return the values to be displayed in the LSform
   *
   * @param[in] $data The values of attribute
   *
   * @retval array The values to be displayed in the LSform
   **/
  public function getFormVal($data) {
    return $data;
  }

}

/*
 * Error Codes
 */
LSerror :: defineError('LSattr_html_select_object_01',
_("LSattr_html_select_object : parameter '%{parameter}' is missing (attribute : %{attr}).")
);
LSerror :: defineError('LSattr_html_select_object_02',
_("LSattr_html_select_object : the value of the parameter value_attribute in the configuration of the attribute %{attrs} is incorrect. Object %{object_type} have no attribute %{value_attribute}.")
);
LSerror :: defineError('LSattr_html_select_object_03',
_("LSattr_html_select_object : more than one object returned corresponding to value %{val} of attribute %{attr}.")
);
LSerror :: defineError('LSattr_html_select_object_04',
_("LSattr_html_select_object : selection of object type %{type} is configured multiple time for attribute %{attr}.")
);
LSerror :: defineError('LSattr_html_select_object_05',
_("LSattr_html_select_object : the value '%{value}' seem to be duplicated in values of the attribute %{attr}.")
);
LSerror :: defineError('LSattr_html_select_object_06',
_("LSattr_html_select_object : selected object %{name} has no attribute %{attr} value, you can't select it.")
);
