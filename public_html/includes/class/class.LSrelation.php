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

class LSrelation {

  private $obj = null;
  private $relationName = null;
  private $config = null;

  public function LSrelation(&$obj,$relationName) {
    $this -> obj =& $obj;
    $this -> relationName = $relationName;
    if (isset($obj->config['LSrelation'][$relationName]) && is_array($obj->config['LSrelation'][$relationName])) {
      $this -> config = $obj->config['LSrelation'][$relationName];
    }
    else {
      LSerror :: addErrorCode('LSrelations_02',array('relation' => $relationName,'LSobject' => $obj -> getType()));
    }
  }

  public function canEdit() {
    return LSsession :: relationCanEdit($this -> obj -> getValue('dn'),$this -> obj -> getType(),$this -> relationName);
  }

  public function listRelatedObjects() {
    if (LSsession :: loadLSobject($this -> config['LSobject'])) {
      $objRel = new $this -> config['LSobject']();
      if (isset($this -> config['list_function'])) {
        if (method_exists($this -> config['LSobject'],$this -> config['list_function'])) {
          return call_user_func_array(array($objRel, $this -> config['list_function']), array(&$this -> obj));
        }
        LSerror :: addErrorCode('LSrelations_01',array('function' => $this -> config['list_function'], 'action' =>  _('listing related objects'), 'relation' => $this -> relationName));
        return False;
      }
      elseif (isset($this -> config['linkAttribute']) && isset($this -> config['linkAttributeValue'])) {
        return $objRel -> listObjectsInRelation($this -> obj, $this -> config['linkAttribute'], $this -> obj -> getType(), $this -> getLinkAttributeValues());
      }
      else {
        LSerror :: addErrorCode('LSrelations_05',array('relation' => $this -> relationName,'LSobject' => $this -> config['LSobject'],'action' => _('listing related objects')));
      }
    }
    else {
      LSerror :: addErrorCode('LSrelations_04',array('relation' => $this -> relationName,'LSobject' => $this -> config['LSobject']));
    }
    return;
  }

  public function getLinkAttributeValues() {
    if (isset($this -> config['linkAttributeOtherValues'])) {
      $linkAttributeValues=$this -> config['linkAttributeOtherValues'];
      $linkAttributeValues[]=$this -> config['linkAttributeValue'];
      return $linkAttributeValues;
    }
    else {
      return $this -> config['linkAttributeValue'];
    }
  }

  public function getRelatedKeyValue() {
    if (LSsession :: loadLSobject($this -> config['LSobject'])) {
      $objRel = new $this -> config['LSobject']();
      if (isset($this -> config['getkeyvalue_function'])) {
        if (method_exists($this -> config['LSobject'],$this -> config['getkeyvalue_function'])) {
          return call_user_func_array(array($objRel, $this -> config['getkeyvalue_function']), array(&$this -> obj));
        }
        LSerror :: addErrorCode('LSrelations_01',array('function' => $this -> config['getkeyvalue_function'], 'action' =>  _('getting key value'), 'relation' => $this -> relationName));
      }
      elseif (isset($this -> config['linkAttribute']) && isset($this -> config['linkAttributeValue'])) {
        return $objRel -> getObjectKeyValueInRelation($this -> obj, $this -> obj -> getType(), $this -> config['linkAttributeValue']);
      }
      else {
        LSerror :: addErrorCode('LSrelations_05',array('relation' => $this -> relationName,'LSobject' => $this -> config['LSobject'],'action' => _('getting key value')));
      }
    }
    else {
      LSerror :: addErrorCode('LSrelations_04',array('relation' => $this -> relationName,'LSobject' => $this -> config['LSobject']));
    }
    return;
  }

  public function getRelatedEditableAttribute() {
    if (isset($this -> config['canEdit_attribute'])) {
      return $this -> config['canEdit_attribute'];
    }
    elseif (isset($this -> config['linkAttribute'])) {
      return $this -> config['linkAttribute'];
    }
    return False;
  }

  public function canEditRelationWithObject($objRel) {
    if (!$this -> canEdit()) return;
    if (isset($this -> config['canEdit_function'])) {
      if (method_exists($objRel,$this -> config['canEdit_function'])) {
        return call_user_func(array($objRel, $this -> config['canEdit_function']));
      }
      LSerror :: addErrorCode('LSrelations_01',array('function' => $this -> config['canEdit_function'], 'action' =>  _('checking right on relation with specific object'), 'relation' => $this -> relationName));
      return False;
    }
    elseif ($this -> getRelatedEditableAttribute()) {
      return LSsession :: canEdit($objRel -> getType(),$objRel -> getDn(),$this -> getRelatedEditableAttribute());
    }
    else {
      LSerror :: addErrorCode('LSrelations_05',array('relation' => $this -> relationName,'LSobject' => $this -> config['LSobject'],'action' => _('checking right on relation with specific object')));
    }
  }

  public function removeRelationWithObject($objRel) {
    if (isset($this -> config['remove_function'])) {
      if (method_exists($this -> config['LSobject'],$this -> config['remove_function'])) {
        return call_user_func_array(array($objRel, $this -> config['remove_function']),array(&$this -> obj));
      }
      LSerror :: addErrorCode('LSrelations_01',array('function' => $this -> config['remove_function'], 'action' =>  _('deleting'), 'relation' => $this -> relationName));
      return False;
    }
    elseif (isset($this -> config['linkAttribute']) && isset($this -> config['linkAttributeValue'])) {
      return $objRel -> deleteOneObjectInRelation($this -> obj, $this -> config['linkAttribute'], $this -> obj -> getType(), $this -> config['linkAttributeValue'], null, $this -> getLinkAttributeValues());
    }
    else {
      LSerror :: addErrorCode('LSrelations_05',array('relation' => $this -> relationName,'LSobject' => $this -> config['LSobject'],'action' => _('removing relation with specific object')));
    }
    return;
  }

  public function renameRelationWithObject($objRel,$oldKeyValue) {
    if (isset($this -> config['rename_function'])) {
      if (method_exists($objRel,$this -> config['rename_function'])) {
        return call_user_func_array(array($objRel, $this -> config['rename_function']), array(&$this -> obj, $oldKeyValue));
      }
      LSerror :: addErrorCode('LSrelations_01',array('function' => $this -> config['rename_function'], 'action' =>  _('renaming'), 'relation' => $this -> relationName));
      return False;
    }
    elseif (isset($this -> config['linkAttribute']) && isset($this -> config['linkAttributeValue'])) {
      return $objRel -> renameOneObjectInRelation($this -> obj, $oldKeyValue, $this -> config['linkAttribute'], $this -> obj -> getType(), $this -> config['linkAttributeValue']);
    }
    else {
      LSerror :: addErrorCode('LSrelations_05',array('relation' => $this -> relationName,'LSobject' => $this -> config['LSobject'],'action' => _('checking right on relation with specific object')));
    }
    return;
  }

  public function updateRelations($listDns) {
    if (LSsession :: loadLSobject($this -> config['LSobject'])) {
      $objRel = new $this -> config['LSobject']();
      if (isset($this -> config['update_function'])) {
        if (method_exists($objRel,$this -> config['update_function'])) {
          return call_user_func_array(array($objRel, $this -> config['update_function']), array(&$this -> obj, $listDns));
        }
        LSerror :: addErrorCode('LSrelations_01',array('function' => $this -> config['update_function'], 'action' =>  _('updating'), 'relation' => $this -> relationName));
      }
      elseif (isset($this -> config['linkAttribute']) && isset($this -> config['linkAttributeValue'])) {
        return $objRel -> updateObjectsInRelation($this -> obj, $listDns, $this -> config['linkAttribute'], $this -> obj -> getType(), $this -> config['linkAttributeValue'],null,$this -> getLinkAttributeValues());
      }
      else {
        LSerror :: addErrorCode('LSrelations_05',array('relation' => $this -> relationName,'LSobject' => $this -> config['LSobject'],'action' => _('updating relations')));
      }
    }
    else {
      LSerror :: addErrorCode('LSrelations_04',array('relation' => $this -> relationName,'LSobject' => $this -> config['LSobject']));
    }
    return;
  }

 /*
  * Méthode chargeant les dépendances d'affichage
  * 
  * @retval void
  */
  public static function loadDependenciesDisplay() {
    if (LSsession :: loadLSclass('LSselect')) {
      LSselect :: loadDependenciesDisplay();
    }
    LSsession :: addJSscript('LSrelation.js');
    LSsession :: addCssFile('LSrelation.css');
    
    LSsession :: addJSconfigParam('LSrelation_labels', array(
      'close_confirm_text'      => _('Do you really want to delete'),
      'close_confirm_title'     => _('Warning'), 
      'close_confirm_validate'  => _('Delete')
    ));
  }
  
 /*
  * Méthode chargeant les informations des LSrelations d'un objet et définissant
  * les variables templates pour l'affichage dans une LSview.
  * 
  * @param[in] LSldapObject L'objet dont on cherche les LSrelations
  * 
  * @retval void
  */ 
  public static function displayInLSview($object) {
    if (($object instanceof LSldapObject) && (is_array($object -> config['LSrelation']))) {
      $LSrelations=array();
      $LSrelations_JSparams=array();
      foreach($object -> config['LSrelation'] as $relationName => $relationConf) {
        if (LSsession :: relationCanAccess($object -> getValue('dn'),$object->getType(),$relationName)) {
          $return=array(
            'label' => __($relationConf['label']),
            'LSobject' => $relationConf['LSobject']
          );
          
          if (isset($relationConf['emptyText'])) {
            $return['emptyText'] = __($relationConf['emptyText']);
          }
          else {
            $return['emptyText'] = _('No object.');
          }
          
          $id=rand();
          $return['id']=$id;
          $LSrelations_JSparams[$id]=array(
            'emptyText' => $return['emptyText']
          );
          $_SESSION['LSrelation'][$id] = array(
            'relationName' => $relationName,
            'objectType' => $object -> getType(),
            'objectDn' => $object -> getDn(),
          );
          $relation = new LSrelation($object, $relationName);
          if ($relation -> canEdit()) {
            $return['actions'][] = array(
              'label' => _('Modify'),
              'url' => 'select.php?LSobject='.$relationConf['LSobject'].'&amp;multiple=1'.($relation -> getRelatedEditableAttribute()?'&amp;editableAttr='.$relation -> getRelatedEditableAttribute():''),
              'action' => 'modify'
            );
          }
          
          $list = $relation -> listRelatedObjects();
          if (is_array($list)) {
            foreach($list as $o) {
              $return['objectList'][] = array(
                'text' => $o -> getDisplayName(NULL,true),
                'dn' => $o -> getDn(),
                'canEdit' => $relation -> canEditRelationWithObject($o)
              );
            }
          }
          else {
            $return['objectList']=array();
          }
          $LSrelations[]=$return;
        }
      }
      
      self :: loadDependenciesDisplay();
      LStemplate :: assign('LSrelations',$LSrelations);
      LSsession :: addJSconfigParam('LSrelations',$LSrelations_JSparams);
    }
  }
  
  public static function ajax_refreshSession(&$return) {
    if ((isset($_REQUEST['id'])) && (isset($_REQUEST['href'])) ) {
      if (isset($_SESSION['LSrelation'][$_REQUEST['id']])) {
        $conf = $_SESSION['LSrelation'][$_REQUEST['id']];
        if (LSsession ::loadLSobject($conf['objectType'])) {
          $object = new $conf['objectType']();
          if (($object -> loadData($conf['objectDn'])) && (isset($object->config['LSrelation'][$conf['relationName']]))) {
            $relation = new LSrelation($object, $conf['relationName']);
            $LSobjectInRelation = $object->config['LSrelation'][$conf['relationName']]['LSobject'];
            if ($relation -> canEdit()) {
              $list = $relation -> listRelatedObjects();
              $_SESSION['LSselect'][$LSobjectInRelation]=array();
              if (is_array($list)) {
                foreach($list as $o) {
                  $_SESSION['LSselect'][$LSobjectInRelation][] = $o -> getDn();
                }
              }
              $return = array(
                'href' => $_REQUEST['href'],
                'id' => $_REQUEST['id']
              );
            }
            else {
              LSerror :: addErrorCode('LSsession_11');
            }
          }
          else {
            LSerror :: addErrorCode('LSsession_12');
          }
        }
        else {
          LSerror :: addErrorCode('LSsession_12');
        }
      }
      else {
        LSerror :: addErrorCode('LSsession_12');
      }
    }
  }
  
  public static function ajax_refreshList(&$data) {
    if (isset($_REQUEST['id'])) {
      if (isset($_SESSION['LSrelation'][$_REQUEST['id']])) {
        $conf = $_SESSION['LSrelation'][$_REQUEST['id']];
        if (LSsession ::loadLSobject($conf['objectType'])) {
          $object = new $conf['objectType']();
          if (($object -> loadData($conf['objectDn'])) && (isset($object->config['LSrelation'][$conf['relationName']]))) {
            $relation = new LSrelation($object, $conf['relationName']);
            $LSobjectInRelation = $object->config['LSrelation'][$conf['relationName']]['LSobject'];
            $relationConf = $object->config['LSrelation'][$conf['relationName']];
            if($relation -> updateRelations($_SESSION['LSselect'][$LSobjectInRelation])) {
              $list = $relation -> listRelatedObjects();
              if (is_array($list)&&(!empty($list))) {
                $data['html']="";
                foreach($list as $o) {
                  if ($relation -> canEditRelationWithObject($o)) {
                    $class=' LSrelation_editable';
                  }
                  else {
                    $class='';
                  }
                  $data['html'].= "<li class='LSrelation'><a href='view.php?LSobject=$LSobjectInRelation&amp;dn=".urlencode($o -> getDn())."' class='LSrelation$class' id='LSrelation_".$_REQUEST['id']."_".$o -> getDn()."'>".$o -> getDisplayName(NULL,true)."</a></li>\n";
                }
              }
              else {
                if (isset($relationConf['emptyText'])) {
                  $data['html'] = "<li>".__($relationConf['emptyText'])."</li>\n";
                }
                else {
                  $data['html'] = "<li>"._('No object.')."</li>\n";
                }
              }
              $data['id'] = $_REQUEST['id'];
            }
            else {
              LSerror :: addErrorCode('LSrelations_03',$relationName);
            }
          }
          else {
            LSerror :: addErrorCode('LSsession_12');
          }
        }
        else {
          LSerror :: addErrorCode('LSsession_12');
        }
      }
      else {
        LSerror :: addErrorCode('LSsession_12');
      }
    }
  }
  
  public static function ajax_deleteByDn(&$data) {
    if ((isset($_REQUEST['id'])) && (isset($_REQUEST['dn']))) {
      if (isset($_SESSION['LSrelation'][$_REQUEST['id']])) {
        $conf = $_SESSION['LSrelation'][$_REQUEST['id']];
        if (LSsession ::loadLSobject($conf['objectType'])) {
          $object = new $conf['objectType']();
          if (($object -> loadData($conf['objectDn'])) && (isset($object->config['LSrelation'][$conf['relationName']]))) {
            $relation = new LSrelation($object, $conf['relationName']);
            if ($relation -> canEdit()) {
              $list = $relation -> listRelatedObjects();
              if (is_array($list)) {
                $ok=false;
                foreach($list as $o) {
                  if($o -> getDn() == $_REQUEST['dn']) {
                    if (!$relation -> canEditRelationWithObject($o)) {
                      LSerror :: addErrorCode('LSsession_11');
                      return;
                    }
                    if (!$relation -> removeRelationWithObject($o)) {
                      LSerror :: addErrorCode('LSrelations_03',$conf['relationName']);
                    }
                    else {
                      $ok = true;
                    }
                    break;
                  }
                }
                if (!$ok) {
                  LSdebug($_REQUEST['value']." introuvé parmi la liste");
                  LSerror :: addErrorCode('LSrelations_03',$conf['relationName']);
                }
                else {
                  $data=array(
                    'dn' => $_REQUEST['dn'],
                    'id' => $_REQUEST['id']
                  );
                }
              }
              else {
                LSerror :: addErrorCode('LSrelations_03',$conf['relationName']);
              }
            }
            else {
              LSerror :: addErrorCode('LSsession_11');
            }
          }
          else {
            LSerror :: addErrorCode('LSsession_12');
          }
        }
        else {
          LSerror :: addErrorCode('LSsession_12');
        }
      }
      else {
        LSerror :: addErrorCode('LSsession_12');
      }
    }
  }
}

/**
 * Error Codes
 **/
LSerror :: defineError('LSrelations_01',
_("LSrelation : The function %{function} for action '%{action}' on the relation %{relation} is unknow.")
);
LSerror :: defineError('LSrelations_02',
_("LSrelation : Relation %{relation} of object type %{LSobject} unknow.")
);
LSerror :: defineError('LSrelations_03',
_("LSrelation : Error during relation update of the relation %{relation}.")
);
LSerror :: defineError('LSrelations_04',
_("LSrelation : Object type %{LSobject} unknow (Relation : %{relation}).")
);
LSerror :: defineError('LSrelations_05',
_("LSrelation : Incomplete configuration for LSrelation %{relation} of object type %{LSobject} for action : %{action}.")
);
