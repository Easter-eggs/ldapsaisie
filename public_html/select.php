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
        LStemplate :: assign('pagetitle',$object -> getLabel());

        $LSsearch = new LSsearch($LSobject,'LSselect');
        $LSsearch -> setParamsFormPostData();
        $LSsearch -> setParam('nbObjectsByPage',NB_LSOBJECT_LIST_SELECT);

        $selectablly=((isset($_REQUEST['selectablly']))?$_REQUEST['selectablly']:0);

        if (is_string($_REQUEST['editableAttr'])) {
          $LSsearch -> setParam(
            'customInfos',
            array (
              'selectablly' => array (
                'function' => array('LSselect','selectablly'),
                'args' => $_REQUEST['editableAttr']
              )
            )
          );
          $selectablly=1;
        }

	if (!empty($_REQUEST['filter64'])) {
		$filter=base64_decode($_REQUEST['filter64'],1);
		if ($filter) {
			$LSsearch -> setParam('filter',$filter);
		}
	}
        $multiple = ((isset($_REQUEST['multiple']))?1:0);

        $searchForm = array (
          'action' => $_SERVER['PHP_SELF'],
          'recursive' => (! LSsession :: isSubDnLSobject($LSobject) && LSsession :: subDnIsEnabled() ),
          'multiple' => $multiple,
          'selectablly' => $selectablly,
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
              'filter64' => $_REQUEST['filter64'],
              'selectablly' => $selectablly,
              'multiple' => $multiple
            )
          )
        );
        LStemplate :: assign('searchForm',$searchForm);

        $LSview_actions=array(
          array (
            'label' => 'Refresh',
            'url' => "object/$LSobject?refresh",
            'action' => 'refresh'
          )
        );
        LStemplate :: assign('LSview_actions',$LSview_actions);

        $LSsearch -> run();
        $page=(isset($_REQUEST['page'])?(int)$_REQUEST['page']:0);
        $page = $LSsearch -> getPage($page);
        LStemplate :: assign('page',$page);
        LStemplate :: assign('LSsearch',$LSsearch);

        LStemplate :: assign('LSobject_list_objectname',$object -> getLabel());

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
