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
  if (LSsession :: globalSearch()) {
    $LSaccess = LSsession :: getLSaccess();
    $pattern = (isset($_REQUEST['pattern'])?$_REQUEST['pattern']:'');
    if (empty($pattern)) {
      LSerror :: addErrorCode(false, _('You must provide pattern for global search.'));
      LSsession :: redirect('index.php');
    }

    $LSview_actions=array();
    $LSview_actions['refresh'] = array (
      'label' => _('Refresh'),
      'url' => 'global_search.php?pattern='.urlencode($pattern).'&refresh=1',
      'action' => 'refresh'
    );
    LStemplate :: assign('LSview_actions', $LSview_actions);

    if (LSsession :: loadLSclass('LSform')) {
      LSform :: loadDependenciesDisplayView();
    }

    $onlyOne = true;
    $onlyOneObject = false;

    if (LSsession :: loadLSclass('LSsearch')) {
      $pages=array();
      foreach ($LSaccess as $LSobject => $label) {
        if ( $LSobject != SELF && LSsession :: loadLSobject($LSobject) ) {
          if (!LSconfig::get("LSobjects.$LSobject.globalSearch", true, 'bool'))
            continue;
          $object = new $LSobject();
          LStemplate :: assign('pagetitle',$object -> getLabel());

          $LSsearch = new LSsearch($LSobject,'LSview');
          $LSsearch -> setParamsFormPostData();

          $LSsearch -> run();

          if ($LSsearch -> total > 0) {
            $page = $LSsearch -> getPage(0);
            LStemplate :: assign('page',$page);
            LStemplate :: assign('LSsearch',$LSsearch);
            $pages[] = LSsession :: fetchTemplate('global_search_one_page.tpl');

            if ($onlyOne) {
              if ($LSsearch -> total > 1) {
                $onlyOne = false;
              }
              else {
                if ($onlyOneObject === false) {
                  $onlyOneObject = array (
                    'LSobject' => $LSobject,
                    'dn' => $page['list'][0] -> dn,
                  );
                }
                else {
                  // More than one LSobject type result with one object found
                  $onlyOne = false;
                }
              }
            }
          }
          $LSsearch -> afterUsingResult();
        }
      }
    }
    else {
      LSsession :: addErrorCode('LSsession_05','LSsearch');
    }

    if ($onlyOne && $onlyOneObject && isset($_REQUEST['LSsearch_submit'])) {
      LSurl :: redirect('object/'.$onlyOneObject['LSobject'].'/'.urlencode($onlyOneObject['dn']));
    }

    LStemplate :: assign('pattern',$pattern);
    LStemplate :: assign('pages',$pages);
    LSsession :: setTemplate('global_search.tpl');
  }
  else {
    LSerror :: addErrorCode('LSsession_11');
  }
}
else {
  LSsession :: setTemplate('login.tpl');
}

// Print template
LSsession :: displayTemplate();
