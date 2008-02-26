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
    
    if ( $GLOBALS['LSsession'] -> loadLSobject($_REQUEST['LSobject']) ) {
        $objectList=array();
        $object = new $_REQUEST['LSobject']();
        
        
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
        
        $list=$object -> listObjects($filter);
        $nbObjects=count($list);

        if ($nbObjects > NB_LSOBJECT_LIST) {
          if (isset($_GET['page'])) {
            $list = array_slice($list, ($_GET['page']) * NB_LSOBJECT_LIST, NB_LSOBJECT_LIST);
            $GLOBALS['Smarty']->assign('LSobject_list_currentpage',$_GET['page']);
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
            if ($c%2==0) {
              $tr='bis';
            }
            else {
              $tr='';
            }
            
            if (is_array($_SESSION['LSselect'][$_REQUEST['LSobject']])) {
              if(in_array($thisObject -> getValue('dn'),$_SESSION['LSselect'][$_REQUEST['LSobject']])) {
                $select = true;
              }
              else {
                $select = false;
              }
            }
            else {
              $select = false;
            }
            
            $objectList[]=array(
              'dn' => $thisObject->getValue('dn'),
              'displayValue' => $thisObject->getDisplayValue(),
              'actions' => $actions,
              'tr' => $tr,
              'select' => $select
            );
          }
        }
        
        
        $GLOBALS['LSsession'] -> addJSscript('LSview.js');
        //$GLOBALS['LSsession'] -> addJSscript('LSselect.js');
        
        $GLOBALS['Smarty']->assign('LSview_search',array(
          'action' => $_SERVER['PHP_SELF'],
          'submit' => _('Rechercher'),
          'LSobject' => $_REQUEST['LSobject']
        ));
        
        $GLOBALS['Smarty']->assign('pagetitle',$object -> getLabel());
        $GLOBALS['Smarty']->assign('LSobject_list_objectname',$object -> getLabel());
        $GLOBALS['Smarty']->assign('LSobject_list_nbresult',$nbObjects);
        $GLOBALS['Smarty']->assign('LSobject_list',$objectList);
        $GLOBALS['Smarty']->assign('LSobject_list_objecttype',$_REQUEST['LSobject']);
        if (isset($_REQUEST['ajax'])) {
          $GLOBALS['LSsession'] -> setTemplate('select_table.tpl');
        }
        else {
          $GLOBALS['LSsession'] -> setTemplate('select.tpl');
        }
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode(1004,$_REQUEST['LSobject']);
      $GLOBALS['LSsession'] -> setTemplate('blank.tpl');
    }
  }
  else {
    $GLOBALS['LSerror'] -> addErrorCode(1012);
    $GLOBALS['LSsession'] -> setTemplate('blank.tpl');
  }
}
else {
  $GLOBALS['LSsession'] -> setTemplate('login.tpl');
}

// Affichage des retours d'erreurs
$GLOBALS['LSsession'] -> displayTemplate();
?>
