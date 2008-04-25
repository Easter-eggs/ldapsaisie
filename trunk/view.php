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

require_once 'includes/functions.php';
require_once 'includes/class/class.LSsession.php';

$GLOBALS['LSsession'] = new LSsession();

if($LSsession -> startLSsession()) {
  if (isset($_REQUEST['LSobject'])) {
    $LSobject = $_REQUEST['LSobject'];
    
    if ( $LSobject == 'SELF' ) {
      $_REQUEST['LSobject'] = $GLOBALS['LSsession']-> LSuserObject -> getType();
      $_REQUEST['dn'] = $GLOBALS['LSsession']-> LSuserObject -> getValue('dn');
    }
    if ( $GLOBALS['LSsession'] -> loadLSobject($_REQUEST['LSobject']) ) {
      if ( isset($_REQUEST['dn']) ) {
        if ($GLOBALS['LSsession'] -> canAccess($_REQUEST['LSobject'],$_REQUEST['dn'])) {
          if ( $GLOBALS['LSsession'] -> canEdit($_REQUEST['LSobject'],$_REQUEST['dn']) ) {
            $LSview_actions[] = array(
              'label' => _('Modifier'),
              'url' =>'modify.php?LSobject='.$_REQUEST['LSobject'].'&amp;dn='.$_REQUEST['dn'],
              'action' => 'modify'
            );
          }
          
          if ($GLOBALS['LSsession'] -> canCreate($_REQUEST['LSobject'])) {
            $LSview_actions[] = array(
              'label' => _('Copier'),
              'url' =>'create.php?LSobject='.$_REQUEST['LSobject'].'&amp;load='.$_REQUEST['dn'],
              'action' => 'copy'
            );
          }
          
          if ($GLOBALS['LSsession'] -> canRemove($_REQUEST['LSobject'],$_REQUEST['dn'])) {
            $LSview_actions[] = array(
              'label' => _('Supprimer'),
              'url' => 'remove.php?LSobject='.$_REQUEST['LSobject'].'&amp;dn='.$_REQUEST['dn'],
              'action' => 'delete'
            );
          }
          
          if ($GLOBALS['LSsession']-> LSuserObject -> getValue('dn') != $_REQUEST['dn']) {
            $object = new $_REQUEST['LSobject']();
            $object -> loadData($_REQUEST['dn']);
            $GLOBALS['Smarty'] -> assign('pagetitle',$object -> getDisplayValue());
          }
          else {
            $object = &$GLOBALS['LSsession']-> LSuserObject;
            $GLOBALS['Smarty'] -> assign('pagetitle',_('Mon compte'));
          }
          
          $view = $object -> getView();
          $view -> displayView();
          
                    // Relations
          if (is_array($object -> config['relations'])) {
            $LSrelations=array();
            foreach($object -> config['relations'] as $relationName => $relationConf) {
              if ($GLOBALS['LSsession'] -> relationCanAccess($object -> getValue('dn'),$relationName)) {
                $return=array('label' => $relationConf['label']);
                
                $id=rand();
                $return['id']=$id;
                $_SESSION['LSrelation'][$id] = array(
                  'relationName' => $relationName,
                  'objectType' => $object -> getType(),
                  'objectDn' => $object -> getDn(),
                );
                if ($GLOBALS['LSsession'] -> relationCanEdit($object -> getValue('dn'),$relationName)) {
                  $return['actions'][] = array(
                    'label' => _('Modifier'),
                    'url' => 'select.php?LSobject='.$relationConf['LSobject'],
                    'action' => 'modify'
                  );
                }
                
                $GLOBALS['LSsession'] -> addJSscript('LSselect.js');
                $GLOBALS['LSsession'] -> addJSscript('LSsmoothbox.js');
                $GLOBALS['LSsession'] -> addCssFile('LSsmoothbox.css');
                $GLOBALS['LSsession'] -> addJSscript('LSrelation.js');
                if($GLOBALS['LSsession'] -> loadLSobject($relationConf['LSobject'])) {
                  if (method_exists($relationConf['LSobject'],$relationConf['list_function'])) {
                    $objRel = new $relationConf['LSobject']();
                    $list = $objRel -> $relationConf['list_function']($object);
                    if (is_array($list)) {
                      foreach($list as $o) {
                        $return['objectList'][] = $o -> getDisplayValue();
                      }
                    }
                    else {
                      $return['objectList']=array();
                    }
                  }
                  else {
                    $GLOBALS['LSerror'] -> addErrorCode(1013,$relationName);
                  }
                  $LSrelations[]=$return;
                }
                else {
                    $GLOBALS['LSerror'] -> addErrorCode(1016,array('relation' => $relationName,'LSobject' => $relationConf['LSobject']));
                }
              }
            }
            $GLOBALS['Smarty'] -> assign('LSrelations',$LSrelations);
          }
          
          $GLOBALS['Smarty'] -> assign('LSview_actions',$LSview_actions);
          $GLOBALS['LSsession'] -> addJSscript('LSsmoothbox.js');
          $GLOBALS['LSsession'] -> addCssFile('LSsmoothbox.css');
          $GLOBALS['LSsession'] -> setTemplate('view.tpl');
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(1011);
        }
      }
      else {
        $objectList=array();
        $object = new $_REQUEST['LSobject']();
        $GLOBALS['Smarty']->assign('pagetitle',$object -> getLabel());
        $GLOBALS['Smarty']->assign('LSobject_list_objectname',$object -> getLabel());
        
        if ($GLOBALS['LSsession'] -> canCreate($_REQUEST['LSobject'])) {
          $LSview_actions[] = array (
            'label' => _('Nouveau'),
            'url' => 'create.php?LSobject='.$_REQUEST['LSobject'],
            'action' => 'create'
          );
          $canCopy=true;
        }
        
        if ( $_REQUEST['LSview_pattern']!='' ) {
          $filter='(|';
          if ( isset($_REQUEST['LSview_approx']) ) {
            foreach ($object -> attrs as $attr_name => $attr_val) {
              $filter.='('.$attr_name.'~='.$_REQUEST['LSview_pattern'].')';
            }
          }
          else {
            foreach ($object -> attrs as $attr_name => $attr_val) {
              $filter.='('.$attr_name.'=*'.$_REQUEST['LSview_pattern'].'*)';
            }              
          }
          $filter.=')';
          $GLOBALS['Smarty']->assign('LSobject_list_filter','filter='.urlencode($filter));
        }
        else if ($_REQUEST['filter']) {
          $filter=urldecode($_REQUEST['filter']);
          $GLOBALS['Smarty']->assign('LSobject_list_filter','filter='.$_REQUEST['filter']);
        }
        else {
          $filter=NULL;
          $GLOBALS['Smarty']->assign('LSobject_list_filter','');
        }
       
       $topDn = $object -> config['container_dn'].','.$GLOBALS['LSsession'] -> topDn;
       
        $list=$object -> listObjects($filter,$topDn);
        $nbObjects=count($list);
        $GLOBALS['Smarty']->assign('LSobject_list_nbresult',$nbObjects);
        if ($nbObjects > NB_LSOBJECT_LIST) {
          if (isset($_REQUEST['page'])) {
            $list = array_slice($list, ($_REQUEST['page']) * NB_LSOBJECT_LIST, NB_LSOBJECT_LIST);
            $GLOBALS['Smarty']->assign('LSobject_list_currentpage',$_REQUEST['page']);
            $GLOBALS['Smarty']->assign('LSobject_list_nbpage',ceil($nbObjects / NB_LSOBJECT_LIST));
          }
          else {
            $list = array_slice($list, 0, NB_LSOBJECT_LIST);
            $GLOBALS['Smarty']->assign('LSobject_list_currentpage',0);
            $GLOBALS['Smarty']->assign('LSobject_list_nbpage',ceil($nbObjects / NB_LSOBJECT_LIST));
          }
        }
        $c=0;
        foreach($list as $thisObject) {
          $c++;
          unset($actions);
          if ($GLOBALS['LSsession'] -> canAccess($_REQUEST['LSobject'],$thisObject->getValue('dn'))) {
            $actions[] = array(
              'label' => _('Voir'),
              'url' =>'view.php?LSobject='.$_REQUEST['LSobject'].'&amp;dn='.$thisObject -> getValue('dn'),
              'action' => 'view'
            );
            
            if ($GLOBALS['LSsession'] -> canEdit($_REQUEST['LSobject'],$thisObject->getValue('dn'))) {
              $actions[]=array(
                'label' => _('Modifier'),
                'url' => 'modify.php?LSobject='.$_REQUEST['LSobject'].'&amp;dn='.$thisObject->getValue('dn'),
                'action' => 'modify'
              );
            }
            
            if ($canCopy) {
              $actions[] = array(
                'label' => _('Copier'),
                'url' =>'create.php?LSobject='.$_REQUEST['LSobject'].'&amp;load='.$thisObject -> getValue('dn'),
                'action' => 'copy'
              );
            }
            
            if ($GLOBALS['LSsession'] -> canRemove($thisObject -> getType(),$thisObject -> getValue('dn'))) {
              $actions[] = array (
                'label' => _('Supprimer'),
                'url' => 'remove.php?LSobject='.$_REQUEST['LSobject'].'&amp;dn='.$thisObject -> getValue('dn'),
                'action' => 'delete'
              );
            }
            
            if ($c%2==0) {
              $tr='bis';
            }
            else {
              $tr='';
            }
            
            $objectList[]=array(
              'dn' => $thisObject->getValue('dn'),
              'displayValue' => $thisObject->getDisplayValue(),
              'actions' => $actions,
              'tr' => $tr
            );
          }
          else {
            debug($thisObject->getValue('dn'));
          }
        }
        $GLOBALS['LSsession'] -> addJSscript('LSview.js');

        
        $GLOBALS['Smarty']->assign('LSview_search',array(
          'action' => $_SERVER['PHP_SELF'],
          'submit' => _('Rechercher'),
          'LSobject' => $_REQUEST['LSobject']
        ));
        
        
        $GLOBALS['Smarty']->assign('_Actions',_('Actions'));
        $GLOBALS['Smarty']->assign('_Modifier',_('Modifier'));
        $GLOBALS['Smarty']->assign('LSobject_list',$objectList);
        $GLOBALS['Smarty']->assign('LSobject_list_objecttype',$_REQUEST['LSobject']);
        $GLOBALS['Smarty'] -> assign('LSview_actions',$LSview_actions);
        $GLOBALS['LSsession'] -> setTemplate('viewList.tpl');
      }
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode(1004,$_REQUEST['LSobject']);
    }
  }
  else {
    $GLOBALS['LSerror'] -> addErrorCode(1012);
  }
}
else {
  $GLOBALS['LSsession'] -> setTemplate('login.tpl');
}

// Affichage des retours d'erreurs
$GLOBALS['LSsession'] -> displayTemplate();
?>
