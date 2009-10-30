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

require_once 'core.php';

if(LSsession :: startLSsession()) {
  if (isset($_REQUEST['LSobject'])) {
    $LSobject = $_REQUEST['LSobject'];
    
    if ( LSsession ::loadLSobject($LSobject) ) {
      if (LSsession :: loadLSclass('LSsearch')) {
        $object = new $LSobject();
        $GLOBALS['Smarty']->assign('pagetitle',$object -> getLabel());
        
        $LSsearch = new LSsearch($LSobject,'LSselect');
        $LSsearch -> setParamsFormPostData();
        $LSsearch -> setParam('nbObjectsByPage',NB_LSOBJECT_LIST_SELECT);
        
        $multiple = ((isset($_REQUEST['multiple']))?1:0);
        
        $searchForm = array (
          'action' => $_SERVER['PHP_SELF'],
          'recursive' => (! LSsession :: isSubDnLSobject($LSobject) ),
          'multiple' => $multiple,
          'labels' => array (
            'submit' => _('Search'),
            'approx' => _('Approximative search'),
            'recursive' => _('Recursive search'),
            'level' => _('Level')
          ),
          'values' => array (
            'pattern' => $LSsearch->getParam('pattern'),
            'approx' => $LSsearch->getParam('approx'),
            'recursive' => $LSsearch->getParam('recursive'),
            'basedn' => $LSsearch->getParam('basedn')
          ),
          'names' => array (
            'submit' => 'LSsearch_submit'
          ),
          'hiddenFields' => array_merge(
            $LSsearch -> getHiddenFieldForm(),
            array(
              'ajax' => 1,
              'multiple' => $multiple
            )
          )
        );
        $GLOBALS['Smarty']->assign('searchForm',$searchForm);
        
        $LSview_actions=array(
          array (
            'label' => 'Refresh',
            'url' => 'view.php?LSobject='.$LSobject.'&amp;refresh',
            'action' => 'refresh'
          )
        );
        $GLOBALS['Smarty']->assign('LSview_actions',$LSview_actions);
        
        $LSsearch -> run();
        $page=(int)$_REQUEST['page'];
        $page = $LSsearch -> getPage($page);
        $GLOBALS['Smarty']->assign('page',$page);
        $GLOBALS['Smarty']->assign('LSsearch',$LSsearch);

        $GLOBALS['Smarty']->assign('LSobject_list_objectname',$object -> getLabel());
        
        if (isset($_REQUEST['ajax'])) {
          LSsession :: setTemplate('select_table.tpl');
        }
        else {
          LSsession :: setTemplate('select.tpl');
        }
        
        LSsession :: setAjaxDisplay();
      }
      else {
        LSsession :: addErrorCode('LSsession_05','LSsearch');
      }
    }
  }
  else {
    LSerror :: addErrorCode('LSsession_12');
  }
}
else {
  LSsession :: setTemplate('login.tpl');
}

// Affichage des retours d'erreurs
LSsession :: displayTemplate();

if (isset($LSsearch)) {
  $LSsearch->afterUsingResult();
}
?>
