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
    if ( $LSobject == 'SELF' ) {
      $LSobject = LSsession :: getLSuserObject() -> getType();
      $dn = LSsession :: getLSuserObjectDn();
    }
    else {
      $dn = isset($_REQUEST['dn'])?urldecode($_REQUEST['dn']):null;
    }
    
    if (LSsession :: in_menu($LSobject) || LSsession :: canAccess($LSobject,$dn)) {
    
      if ( LSsession :: loadLSobject($LSobject) ) {
        // Affichage d'un objet
        if ( $dn!='' ) {
          if (LSsession :: canAccess($LSobject,$dn)) {
            if ( LSsession :: canEdit($LSobject,$dn) ) {
              $LSview_actions[] = array(
                'label' => _('Modify'),
                'url' =>'modify.php?LSobject='.$LSobject.'&amp;dn='.urlencode($dn),
                'action' => 'modify'
              );
            }
            
            if (LSsession :: canCreate($LSobject)) {
              $LSview_actions[] = array(
                'label' => _('Copy'),
                'url' =>'create.php?LSobject='.$LSobject.'&amp;load='.urlencode($dn),
                'action' => 'copy'
              );
            }
            
            if (LSsession :: canRemove($LSobject,$dn)) {
              $LSview_actions[] = array(
                'label' => _('Delete'),
                'url' => 'remove.php?LSobject='.$LSobject.'&amp;dn='.urlencode($dn),
                'action' => 'delete'
              );
            }

            // Custum Actions
            $customActionsConfig = LSconfig :: get('LSobjects.'.$LSobject.'.customActions');
            if (is_array($customActionsConfig)) {
              foreach($customActionsConfig as $name => $config) {
                if (LSsession :: canExecuteCustomAction($dn,$LSobject,$name)) {
                  $LSview_actions[] = array (
                    'label' => ((isset($config['label']))?__($config['label']):__($name)),
                    'hideLabel' => ((isset($config['hideLabel']))?$config['hideLabel']:False),
                    'helpInfo' => ((isset($config['helpInfo']))?__($config['helpInfo']):False),
                    'url' => 'custom_action.php?LSobject='.$LSobject.'&amp;dn='.urlencode($dn).'&amp;customAction='.$name,
                    'action' => ((isset($config['icon']))?$config['icon']:'generate'),
                    'class' => 'LScustomActions'.(($config['noConfirmation'])?' LScustomActions_noConfirmation':'')
                  );
                }
              }
            }
            
            if (LSsession :: getLSuserObjectDn() != $dn) {
              $object = new $LSobject();
              $object -> loadData($dn);
              LStemplate :: assign('pagetitle',$object -> getDisplayName());
            }
            else {
              $object = LSsession :: getLSuserObject();
              LStemplate :: assign('pagetitle',_('My account'));
            }

            LStemplate :: assign('LSldapObject',$object);
            
            $view = $object -> getView();
            $view -> displayView();
            
            // LSrelations
            if (LSsession :: loadLSclass('LSrelation')) {
              LSrelation :: displayInLSview($object);
            }
            
            LStemplate :: assign('LSview_actions',$LSview_actions);
            LSsession :: setTemplate('view.tpl');
          }
          else {
            LSerror :: addErrorCode('LSsession_11');
          }
        }
        // Affichage d'une liste d'un type d'objet
        elseif (LSsession :: loadLSclass('LSsearch')) {
          $object = new $LSobject();
          LStemplate :: assign('pagetitle',$object -> getLabel());
          
          $LSsearch = new LSsearch($LSobject,'LSview');
          $LSsearch -> setParam('extraDisplayedColumns',True);
          $LSsearch -> setParamsFormPostData();
          
          $searchForm = array (
            'action' => $_SERVER['PHP_SELF'],
            'recursive' => (! LSsession :: isSubDnLSobject($LSobject) && LSsession :: subDnIsEnabled() ),
            'labels' => array (
              'submit' => _('Search'),
              'approx' => _('Approximative search'),
              'recursive' => _('Recursive search')
            ),
            'values' => array (
              'pattern' => $LSsearch->getParam('pattern'),
              'approx' => $LSsearch->getParam('approx'),
              'recursive' => $LSsearch->getParam('recursive')
            ),
            'names' => array (
              'submit' => 'LSsearch_submit'
            ),
            'hiddenFields' => $LSsearch -> getHiddenFieldForm(),
            'predefinedFilter' => $LSsearch->getParam('predefinedFilter')
          );
          LStemplate :: assign('searchForm',$searchForm);
          
          $LSview_actions=array();
          if(LSsession :: canCreate($LSobject)) {
            $LSview_actions['create'] = array (
              'label' => 'New',
              'url' => 'create.php?LSobject='.$LSobject,
              'action' => 'create'
            );
            if ($object -> listValidIOformats()) {
              $LSview_actions['import'] = array (
                'label' => 'Import',
                'url' => 'import.php?LSobject='.$LSobject,
                'action' => 'import'
              );
            }
          }
          $LSview_actions['refresh'] = array (
            'label' => 'Refresh',
            'url' => 'view.php?LSobject='.$LSobject.'&amp;refresh',
            'action' => 'refresh'
          );
          /*$LSview_actions['purge'] = array (
            'label' => 'Purge the cache',
            'url' => 'view.php?LSobject='.$LSobject.'&amp;LSsearchPurgeSession',
            'action' => 'delete'
          );*/

          // Custum Actions
          $customActionsConfig = LSconfig :: get('LSobjects.'.$LSobject.'.LSsearch.customActions');
          if (is_array($customActionsConfig)) {
            foreach($customActionsConfig as $name => $config) {
              if (LSsession :: canExecuteLSsearchCustomAction($LSsearch,$name)) {
                $LSview_actions[] = array (
                  'label' => ((isset($config['label']))?__($config['label']):__($name)),
                  'hideLabel' => ((isset($config['hideLabel']))?$config['hideLabel']:False),
                  'helpInfo' => ((isset($config['helpInfo']))?__($config['helpInfo']):False),
                  'url' => 'custom_search_action.php?LSobject='.$LSobject.'&amp;customAction='.$name,
                  'action' => ((isset($config['icon']))?$config['icon']:'generate'),
                  'class' => 'LScustomActions'.(($config['noConfirmation'])?' LScustomActions_noConfirmation':'')
                );
              }
            }
          }

          LStemplate :: assign('LSview_actions',$LSview_actions);
          
          $LSsearch -> run();
          
          $LSsearch -> redirectWhenOnlyOneResult();
          
          $page=(isset($_REQUEST['page'])?(int)$_REQUEST['page']:0);
          $page = $LSsearch -> getPage($page);
          LStemplate :: assign('page',$page);
          LStemplate :: assign('LSsearch',$LSsearch);
          
          if (LSsession :: loadLSclass('LSform')) {
            LSform :: loadDependenciesDisplayView();
          }
          
          LSsession :: setTemplate('viewSearch.tpl');
        }
        else {
          LSsession :: addErrorCode('LSsession_05','LSsearch');
        }
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
  LSsession :: setTemplate('login.tpl');
}
// Affichage des retours d'erreurs
LSsession :: displayTemplate();

if (isset($LSsearch)) {
  $LSsearch->afterUsingResult();
}

