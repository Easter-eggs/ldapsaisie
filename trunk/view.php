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

define('NB_LSOBJECT_LIST',20);

require_once 'includes/functions.php';
require_once 'includes/class/class.LSsession.php';

$GLOBALS['LSsession'] = new LSsession();

if($LSsession -> startLSsession()) {
  $LSobject = $_GET['LSobject'];
  
  if ( $LSobject == 'SELF' ) {
    if ($GLOBALS['LSsession'] -> canAccess($GLOBALS['LSsession']-> LSuserObject -> getType(),$GLOBALS['LSsession']-> LSuserObject -> getValue('dn'))) {
      if ( $GLOBALS['LSsession'] -> canEdit($GLOBALS['LSsession']-> LSuserObject -> getType(),$GLOBALS['LSsession']-> LSuserObject -> getValue('dn'))) {
        $GLOBALS['Smarty'] -> assign('LSform_canEdit',true);
      }
      $GLOBALS['Smarty'] -> assign('pagetitle',_('Mon compte'));
      $form = $GLOBALS['LSsession']-> LSuserObject -> getView();
      $form -> displayView();
      $GLOBALS['LSsession'] -> setTemplate('view.tpl');
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode(1004,$_GET['LSobject']);
    }
  }
  else {
    if ( $GLOBALS['LSsession'] -> loadLSobject($_GET['LSobject']) ) {
      if ( isset($_GET['dn']) ) {
        if ($GLOBALS['LSsession'] -> canAccess($_GET['LSobject'],$_GET['dn'])) {
          if ( $GLOBALS['LSsession'] -> canEdit($_GET['LSobject'],$_GET['dn']) ) {
            $GLOBALS['Smarty'] -> assign('LSform_canEdit','true');
          }
          $object = new $_GET['LSobject']();
          $object -> loadData($_GET['dn']);
          $view = $object -> getView();
          $view -> displayView();
          $GLOBALS['LSsession'] -> setTemplate('view.tpl');
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(1011);
        }
      }
      else {
        $objectList=array();
        $object = new $_GET['LSobject']();
        $GLOBALS['Smarty']->assign('pagetitle',$object -> getLabel());
        $GLOBALS['Smarty']->assign('LSobject_list_objectname',$object -> getLabel());
        $list=$object -> listObjects();
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
        foreach($list as $thisObject) {
          if ($GLOBALS['LSsession'] -> canAccess($_GET['LSobject'],$thisObject->getValue('dn'))) {
            $objectList[]=array(
              'dn' => $thisObject->getValue('dn'),
              'displayValue' => $thisObject->getDisplayValue(),
              'canEdit' => $GLOBALS['LSsession'] -> canEdit($_GET['LSobject'],$thisObject->getValue('dn'))
            );
          }
          else {
            debug($thisObject->getValue('dn'));
          }
        }
        $GLOBALS['LSsession'] -> addJSscript('LSview.js');
        
        $GLOBALS['Smarty']->assign('_Actions',_('Actions'));
        $GLOBALS['Smarty']->assign('_Modifier',_('Modifier'));
        $GLOBALS['Smarty']->assign('LSobject_list',$objectList);
        $GLOBALS['Smarty']->assign('LSobject_list_objecttype',$_GET['LSobject']);
        $GLOBALS['LSsession'] -> setTemplate('viewList.tpl');
      }
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode(1004,$_GET['LSobject']);
    }
  }
}
else {
  $GLOBALS['LSsession'] -> setTemplate('login.tpl');
}

// Affichage des retours d'erreurs
$GLOBALS['LSsession'] -> displayTemplate();
?>
