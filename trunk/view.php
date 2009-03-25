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
    $dn = $_REQUEST['dn'];
    
    if (LSsession :: in_menu($LSobject)) {
    
      if ( $LSobject == 'SELF' ) {
        $LSobject = LSsession :: getLSuserObject() -> getType();
        $dn = LSsession :: getLSuserObjectDn();
      }
      
      if ( LSsession :: loadLSobject($LSobject) ) {
        // Affichage d'un objet
        if ( $dn!='' ) {
          if (LSsession :: canAccess($LSobject,$dn)) {
            if ( LSsession :: canEdit($LSobject,$dn) ) {
              $LSview_actions[] = array(
                'label' => _('Modify'),
                'url' =>'modify.php?LSobject='.$LSobject.'&amp;dn='.$dn,
                'action' => 'modify'
              );
            }
            
            if (LSsession :: canCreate($LSobject)) {
              $LSview_actions[] = array(
                'label' => _('Copy'),
                'url' =>'create.php?LSobject='.$LSobject.'&amp;load='.$dn,
                'action' => 'copy'
              );
            }
            
            if (LSsession :: canRemove($LSobject,$dn)) {
              $LSview_actions[] = array(
                'label' => _('Delete'),
                'url' => 'remove.php?LSobject='.$LSobject.'&amp;dn='.$dn,
                'action' => 'delete'
              );
            }
            
            if (LSsession :: getLSuserObjectDn() != $dn) {
              $object = new $LSobject();
              $object -> loadData($dn);
              $GLOBALS['Smarty'] -> assign('pagetitle',$object -> getDisplayName());
            }
            else {
              $object = LSsession :: getLSuserObject();
              $GLOBALS['Smarty'] -> assign('pagetitle',_('My account'));
            }
            
            $view = $object -> getView();
            $view -> displayView();
            
            // LSrelations
            if (LSsession :: loadLSclass('LSrelation')) {
              LSrelation :: displayInLSview($object);
            }
            
            $GLOBALS['Smarty'] -> assign('LSview_actions',$LSview_actions);
            LSsession :: setTemplate('view.tpl');
          }
          else {
            LSerror :: addErrorCode('LSsession_11');
          }
        }
        // Affichage d'une liste d'un type d'objet
        else {
          $objectList=array();
          $object = new $LSobject();
          
          $GLOBALS['Smarty']->assign('pagetitle',$object -> getLabel());
          $GLOBALS['Smarty']->assign('LSobject_list_objectname',$object -> getLabel());
          
          if (isset($_SESSION['LSsession']['LSsearch'][$LSobject])) {
            $filter = $_SESSION['LSsession']['LSsearch'][$LSobject]['filter'];
            $params = $_SESSION['LSsession']['LSsearch'][$LSobject]['params'];
            $pattern = $_SESSION['LSsession']['LSsearch'][$LSobject]['pattern'];
            $recur = $_SESSION['LSsession']['LSsearch'][$LSobject]['recur'];
            if ($recur) {
              $topDn = LSsession :: getTopDn();
            }
            else {
              $topDn = $object -> config['container_dn'].','.LSsession :: getTopDn();
            }
            $approx = $_SESSION['LSsession']['LSsearch'][$LSobject]['approx'];
            $orderby = $_SESSION['LSsession']['LSsearch'][$LSobject]['orderby'];
            $ordersense = $_SESSION['LSsession']['LSsearch'][$LSobject]['ordersense'];
            $doSubDn = $_SESSION['LSsession']['LSsearch'][$LSobject]['doSubDn'];
          }
          else {
            $filter = NULL;
            $topDn = $object -> config['container_dn'].','.LSsession :: getTopDn();
            $params = array('scope' => 'one');
            $pattern = false;
            $recur = false;
            $approx = false;
            $orderby = false;
            $_REQUEST['orderby']=LSconfig :: get("LSobjects.$LSobject.orderby");
            $ordersense = 'ASC';
            $subDnLdapServer = LSsession :: getSubDnLdapServer();
            $doSubDn = (($subDnLdapServer)&&(!LSsession :: isSubDnLSobject($LSobject)));
          }
          
          if (isset($_REQUEST['LSview_search_submit'])) {
            if (isset($_REQUEST['LSview_pattern']) && ($_REQUEST['LSview_pattern']!=$pattern)) {
              $pattern = $_REQUEST['LSview_pattern'];
            }

            $approx = (isset($_REQUEST['LSview_approx']));
            
            if ($pattern && $pattern!='') {
              $filter=$object -> getPatternFilter($pattern,$approx);
            }
            else {
              $filter = NULL;
            }
            
            if (isset($_REQUEST['LSview_recur'])) {
              $recur = true;
              $params['scope'] = 'sub';
              $topDn = LSsession :: getTopDn();
            }
            else {
              $recur = false;
              $params['scope'] = 'one';
              $topDn = $object -> config['container_dn'].','.LSsession :: getTopDn();
            }
          }
          
          $sort=false;
          if ((isset($_REQUEST['orderby']))) {
            $possible_values= array('displayName','subDn');
            if (in_array($_REQUEST['orderby'],$possible_values)) {
              $sort=true;
              if ($orderby==$_REQUEST['orderby']) {
                $ordersense = ($ordersense=='ASC')?'DESC':'ASC';
              }
              else {
                $ordersense = 'ASC';
              }
              $orderby=$_REQUEST['orderby'];
            }
          }
          
          $GLOBALS['Smarty']->assign('LSobject_list_subDn',$doSubDn);
          
          // Sauvegarde en Session
          $_SESSION['LSsession']['LSsearch'][$LSobject] = array(
            'filter' => $filter,
            'topDn' => $topDn,
            'params' => $params,
            'pattern' => $pattern,
            'recur' => $recur,
            'approx' => $approx,
            'orderby' => $orderby,
            'ordersense' => $ordersense,
            'doSubDn' => $doSubDn
          );

          $GLOBALS['Smarty']->assign('LSview_search_pattern',$pattern);

          if ($recur) {
            $GLOBALS['Smarty']->assign('LSview_search_recur',true);
          }
          if ($approx) {
            $GLOBALS['Smarty']->assign('LSview_search_approx',true);
          }
          
          // Hidden fields
          $GLOBALS['Smarty']->assign('LSview_search_hidden_fields',array(
            'LSobject' => $LSobject,
            'LSview_search_submit' => 1
          ));
          
          // Hash de la recherche déterminer à partir des paramètres de la recherche
          $hash = mhash (MHASH_MD5, 
            print_r(
              array(
                'LSobject' => $LSobject,
                'filter' => $filter,
                'topDn' => $topDn,
                'params' => $params
              ),
              true
            )
          );
          
          if ((LSsession :: cacheSearch()) && isset($_SESSION['LSsession']['LSsearch'][$hash]) && (!isset($_REQUEST['refresh']))) {
            // On affiche à partir du cache
            $searchData=$_SESSION['LSsession']['LSsearch'][$hash];
            LSdebug('Find : From cache');
            if(!isset($searchData['LSview_actions']['create'])) {
              LSdebug('Recherche : Check Create()');
              if (LSsession :: canCreate($LSobject)) {
                $searchData['LSview_actions']['create'] = array (
                  'label' => 'New',
                  'url' => 'create.php?LSobject='.$LSobject,
                  'action' => 'create'
                );
              }
              else {
                $searchData['LSview_actions']['create'] = false;
              }
              $_SESSION['LSsession']['LSsearch'][$hash]=$searchData;
            }
          }
          else { // Load
            LSdebug('Find : Load');
            if (LSsession :: canCreate($LSobject)) {
              $LSview_actions['create'] = array (
                'label' => 'New',
                'url' => 'create.php?LSobject='.$LSobject,
                'action' => 'create'
              );
              $canCopy=true;
            }
            else {
              $LSview_actions['create'] = false;
            }
            $LSview_actions['refresh'] = array (
              'label' => 'Refresh',
              'url' => 'view.php?LSobject='.$LSobject.'&amp;refresh',
              'action' => 'refresh'
            );
            
            $list=$object -> listObjectsName($filter,$topDn,$params);

            $nbObjects=0;
            foreach($list as $objDn => $objName) {
              if (LSsession :: canAccess($LSobject,$objDn)) {
                $subDn_name=false;
                if ($doSubDn) {
                  $subDn_name = $object -> getSubDnName($objDn);
                }
                $nbObjects++;

                
                $objectList[]=array(
                  'dn' => $objDn,
                  'displayName' => $objName,
                  'subDn' => $subDn_name
                );
              }
            }
            
            $searchData['LSobject_list_nbresult']=$nbObjects;
            
            $searchData['objectList']=$objectList;
            $searchData['LSview_actions'] = $LSview_actions;
            if ($orderby) {
              $sort=true;
            }
          } // Fin Load
          
          if ((!isset($searchData['objectList'][0]['actions']))&&(!empty($searchData['objectList']))) {
            LSdebug('Load actions');
            for($i=0;$i<$searchData['LSobject_list_nbresult'];$i++) {
              $actions=array();
              
              $actions[] = array(
                'label' => _('View'),
                'url' =>'view.php?LSobject='.$LSobject.'&amp;dn='.$searchData['objectList'][$i]['dn'],
                'action' => 'view'
              );
              
              if (LSsession :: canEdit($LSobject,$searchData['objectList'][$i]['dn'])) {
                $actions[]=array(
                  'label' => _('Modify'),
                  'url' => 'modify.php?LSobject='.$LSobject.'&amp;dn='.$searchData['objectList'][$i]['dn'],
                  'action' => 'modify'
                );
              }
              
              if ($canCopy) {
                $actions[] = array(
                  'label' => _('Copy'),
                  'url' =>'create.php?LSobject='.$LSobject.'&amp;load='.$searchData['objectList'][$i]['dn'],
                  'action' => 'copy'
                );
              }
              
              if (LSsession :: canRemove($LSobject,$searchData['objectList'][$i]['dn'])) {
                $actions[] = array (
                  'label' => _('Delete'),
                  'url' => 'remove.php?LSobject='.$LSobject.'&amp;dn='.$searchData['objectList'][$i]['dn'],
                  'action' => 'delete'
                );
              }
              $searchData['objectList'][$i]['actions']=$actions;
            }
          }
          
          $GLOBALS['Smarty']->assign('LSobject_list_nbresult',$searchData['LSobject_list_nbresult']);
          
          // Order by if $sort
          if ($sort) {
            function sortBy($a,$b) {
              global $ordersense;
              global $orderby;
              
              if ($ordersense=='ASC') {
                $sense = -1;
              }
              else {
                $sense = 1;
              }
              
              if ($a == $b) return 0;
              $sort = array($a[$orderby],$b[$orderby]);
              sort($sort);
              if ($sort[0]==$a[$orderby])
                return 1*$sense;
              return -1*$sense;
            }
            if (!uasort($searchData['objectList'],'sortBy')) {
              LSdebug('Error while sorting.');
            }
          } // Fin Order by
          $GLOBALS['Smarty']->assign('LSobject_list_orderby',$orderby);
          $GLOBALS['Smarty']->assign('LSobject_list_ordersense',$ordersense);
          
          // Mise en cache
          if (LSsession :: cacheSearch()) {
            $_SESSION['LSsession']['LSsearch'][$hash]=$searchData;
          }
          
          // Pagination
          if ($searchData['LSobject_list_nbresult'] > NB_LSOBJECT_LIST) {
            if (isset($_REQUEST['page'])) {
              $searchData['objectList'] = array_slice($searchData['objectList'], ($_REQUEST['page']) * NB_LSOBJECT_LIST, NB_LSOBJECT_LIST);
              $GLOBALS['Smarty']->assign('LSobject_list_currentpage',$_REQUEST['page']);
              
            }
            else {
              $searchData['objectList'] = array_slice($searchData['objectList'], 0, NB_LSOBJECT_LIST);
              $GLOBALS['Smarty']->assign('LSobject_list_currentpage',0);
            }
            $searchData['LSobject_list_nbpage']=ceil($searchData['LSobject_list_nbresult'] / NB_LSOBJECT_LIST);
            $GLOBALS['Smarty']->assign('LSobject_list_nbpage',$searchData['LSobject_list_nbpage']);
          } // Fin Pagination
          
          if (LSsession :: loadLSclass('LSform')) {
            LSform :: loadDependenciesDisplayView();
          }
          
          $GLOBALS['Smarty']->assign('LSview_search',array(
            'action' => $_SERVER['PHP_SELF'],
            'submit' => _('Search'),
            'LSobject' => $LSobject
          ));
          
          $GLOBALS['Smarty']->assign('LSview_search_recur_label',_('Recursive search'));
          $GLOBALS['Smarty']->assign('LSview_search_approx_label',_('Approximative search'));

          $GLOBALS['Smarty']->assign('LSobject_list_without_result_label',_("This search didn't get any result."));
          $GLOBALS['Smarty']->assign('_Actions',_('Actions'));
          $GLOBALS['Smarty']->assign('_Modifier',_('Modify'));
          $GLOBALS['Smarty']->assign('LSobject_list',$searchData['objectList']);
          $GLOBALS['Smarty']->assign('LSobject_list_objecttype',$LSobject);
          $GLOBALS['Smarty'] -> assign('LSview_actions',$searchData['LSview_actions']);
          LSsession :: setTemplate('viewList.tpl');
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
?>
