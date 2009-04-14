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
          if (LSsession :: relationCanEdit($object -> getValue('dn'),$object->getType(),$relationName)) {
            $return['actions'][] = array(
              'label' => _('Modify'),
              'url' => 'select.php?LSobject='.$relationConf['LSobject'].'&amp;multiple=1',
              'action' => 'modify'
            );
          }
          
          if (LSsession :: loadLSclass('LSrelation')) {
            LSrelation :: loadDependenciesDisplay();
          }
          
          if(LSsession :: loadLSobject($relationConf['LSobject'])) {
            if (method_exists($relationConf['LSobject'],$relationConf['list_function'])) {
              $objRel = new $relationConf['LSobject']();
              $list = $objRel -> $relationConf['list_function']($object);
              if (is_array($list)) {
                foreach($list as $o) {
                  $o_infos = array(
                    'text' => $o -> getDisplayName(NULL,true),
                    'dn' => $o -> getDn()
                  );
                  $return['objectList'][] = $o_infos;
                }
              }
              else {
                $return['objectList']=array();
              }
            }
            else {
              LSerror :: addErrorCode('LSrelations_01',$relationName);
            }
            $LSrelations[]=$return;
          }
          else {
              LSerror :: addErrorCode('LSrelations_04',array('relation' => $relationName,'LSobject' => $relationConf['LSobject']));
          }
        }
      }
      
      $GLOBALS['Smarty'] -> assign('LSrelations',$LSrelations);
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
            $relationConf = $object->config['LSrelation'][$conf['relationName']];
            if (LSsession ::loadLSobject($relationConf['LSobject'])) {
              if (LSsession :: relationCanEdit($object -> getValue('dn'),$object -> getType(),$conf['relationName'])) {
                if (method_exists($relationConf['LSobject'],$relationConf['list_function'])) {
                  $objRel = new $relationConf['LSobject']();
                  $list = $objRel -> $relationConf['list_function']($object);
                  $_SESSION['LSselect'][$relationConf['LSobject']]=array();
                  if (is_array($list)) {
                    foreach($list as $o) {
                      $_SESSION['LSselect'][$relationConf['LSobject']][] = $o -> getDn();
                    }
                  }
                  $return = array(
                    'href' => $_REQUEST['href'],
                    'id' => $_REQUEST['id']
                  );
                }
                else {
                  LSerror :: addErrorCode('LSrelations_01',$relationName);
                }
              }
              else {
                LSerror :: addErrorCode('LSsession_11');
              }
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
            $relationConf = $object->config['LSrelation'][$conf['relationName']];
            if (LSsession ::loadLSobject($relationConf['LSobject'])) {
              if (LSsession :: relationCanEdit($object -> getValue('dn'),$object -> getType(),$conf['relationName'])) {
                if (is_array($_SESSION['LSselect'][$relationConf['LSobject']])) {
                  if (method_exists($relationConf['LSobject'],$relationConf['update_function'])) {
                    $objRel = new $relationConf['LSobject']();
                    if($objRel -> $relationConf['update_function']($object,$_SESSION['LSselect'][$relationConf['LSobject']])) {
                      if (method_exists($relationConf['LSobject'],$relationConf['list_function'])) {
                        $list = $objRel -> $relationConf['list_function']($object);
                        if (is_array($list)&&(!empty($list))) {
                          foreach($list as $o) {
                            $data['html'].= "<li class='LSrelation'><a href='view.php?LSobject=".$relationConf['LSobject']."&amp;dn=".$o -> getDn()."' class='LSrelation' id='".$o -> getDn()."'>".$o -> getDisplayName(NULL,true)."</a></li>\n";
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
                        LSerror :: addErrorCode('LSrelations_01',$relationName);
                      }
                    }
                    else {
                      LSerror :: addErrorCode('LSrelations_03',$relationName);
                    }
                  }
                  else {
                    LSerror :: addErrorCode('LSrelations_02',$relationName);
                  }
                }
              }
              else {
                LSerror :: addErrorCode('LSsession_11');
              }
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
            $relationConf = $object->config['LSrelation'][$conf['relationName']];
            if (LSsession ::loadLSobject($relationConf['LSobject'])) {
              if (LSsession :: relationCanEdit($object -> getValue('dn'),$object -> getType(),$conf['relationName'])) {
                if (method_exists($relationConf['LSobject'],$relationConf['list_function'])) {
                  $objRel = new $relationConf['LSobject']();
                  $list = $objRel -> $relationConf['list_function']($object);
                  if (is_array($list)) {
                    $ok=false;
                    foreach($list as $o) {
                      if($o -> getDn() == $_REQUEST['dn']) {
                        if (!$o -> $relationConf['remove_function']($object)) {
                          LSerror :: addErrorCode('LSrelations_03',$conf['relationName']);
                        }
                        else {
                          $ok = true;
                        }
                      }
                    }
                    if (!$ok) {
                      LSdebug($_REQUEST['value']." introuvé parmi la liste");
                      LSerror :: addErrorCode('LSrelations_03',$conf['relationName']);
                    }
                    else {
                      $data=array(
                        'dn' => $_REQUEST['dn']
                      );
                    }
                  }
                  else {
                    LSerror :: addErrorCode('LSrelations_03',$conf['relationName']);
                  }
                }
                else {
                  LSerror :: addErrorCode('LSrelations_01',$conf['relationName']);
                }
              }
              else {
                LSerror :: addErrorCode('LSsession_11');
              }
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

?>
