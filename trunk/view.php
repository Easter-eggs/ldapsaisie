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
    $dn = $_REQUEST['dn'];
    
    if ($GLOBALS['LSsession'] -> in_menu($LSobject)) {
    
      if ( $LSobject == 'SELF' ) {
        $LSobject = $GLOBALS['LSsession']-> LSuserObject -> getType();
        $dn = $GLOBALS['LSsession']-> LSuserObject -> getValue('dn');
      }
      
      if ( $GLOBALS['LSsession'] -> loadLSobject($LSobject) ) {
        // Affichage d'un objet
        if ( $dn!='' ) {
          if ($GLOBALS['LSsession'] -> canAccess($LSobject,$dn)) {
            if ( $GLOBALS['LSsession'] -> canEdit($LSobject,$dn) ) {
              $LSview_actions[] = array(
                'label' => _('Modifier'),
                'url' =>'modify.php?LSobject='.$LSobject.'&amp;dn='.$dn,
                'action' => 'modify'
              );
            }
            
            if ($GLOBALS['LSsession'] -> canCreate($LSobject)) {
              $LSview_actions[] = array(
                'label' => _('Copier'),
                'url' =>'create.php?LSobject='.$LSobject.'&amp;load='.$dn,
                'action' => 'copy'
              );
            }
            
            if ($GLOBALS['LSsession'] -> canRemove($LSobject,$dn)) {
              $LSview_actions[] = array(
                'label' => _('Supprimer'),
                'url' => 'remove.php?LSobject='.$LSobject.'&amp;dn='.$dn,
                'action' => 'delete'
              );
            }
            
            if ($GLOBALS['LSsession']-> LSuserObject -> getValue('dn') != $dn) {
              $object = new $LSobject();
              $object -> loadData($dn);
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
              $LSrelations_JSparams=array();
              foreach($object -> config['relations'] as $relationName => $relationConf) {
                if ($GLOBALS['LSsession'] -> relationCanAccess($object -> getValue('dn'),$LSobject,$relationName)) {
                  $return=array(
                    'label' => $relationConf['label'],
                    'LSobject' => $relationConf['LSobject']
                  );
                  
                  if (isset($relationConf['emptyText'])) {
                    $return['emptyText'] = $relationConf['emptyText'];
                  }
                  else {
                    $return['emptyText'] = _('Aucun objet en relation.');
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
                  if ($GLOBALS['LSsession'] -> relationCanEdit($object -> getValue('dn'),$LSobject,$relationName)) {
                    $return['actions'][] = array(
                      'label' => _('Modifier'),
                      'url' => 'select.php?LSobject='.$relationConf['LSobject'].'&amp;multiple=1',
                      'action' => 'modify'
                    );
                  }
                  
                  $GLOBALS['LSsession'] -> addJSscript('LSselect.js');
                  $GLOBALS['LSsession'] -> addCssFile('LSselect.css');
                  $GLOBALS['LSsession'] -> addJSscript('LSsmoothbox.js');
                  $GLOBALS['LSsession'] -> addCssFile('LSsmoothbox.css');
                  $GLOBALS['LSsession'] -> addJSscript('LSrelation.js');
                  $GLOBALS['LSsession'] -> addCssFile('LSrelation.css');
                  
                  if($GLOBALS['LSsession'] -> loadLSobject($relationConf['LSobject'])) {
                    if (method_exists($relationConf['LSobject'],$relationConf['list_function'])) {
                      $objRel = new $relationConf['LSobject']();
                      $list = $objRel -> $relationConf['list_function']($object);
                      if (is_array($list)) {
                        foreach($list as $o) {
                          $o_infos = array(
                            'text' => $o -> getDisplayValue(NULL,true),
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
                      $GLOBALS['LSerror'] -> addErrorCode(1013,$relationName);
                    }
                    $LSrelations[]=$return;
                  }
                  else {
                      $GLOBALS['LSerror'] -> addErrorCode(1016,array('relation' => $relationName,'LSobject' => $relationConf['LSobject']));
                  }
                }
              }
              
              $GLOBALS['LSsession'] -> addJSscript('LSconfirmBox.js');
              $GLOBALS['LSsession'] -> addCssFile('LSconfirmBox.css');
              $GLOBALS['Smarty'] -> assign('LSrelations',$LSrelations);
              $GLOBALS['LSsession'] -> addJSconfigParam('LSrelations',$LSrelations_JSparams);
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
              $topDn = $GLOBALS['LSsession'] -> topDn;
            }
            else {
              $topDn = $object -> config['container_dn'].','.$GLOBALS['LSsession'] -> topDn;
            }
            $approx = $_SESSION['LSsession']['LSsearch'][$LSobject]['approx'];
            $orderby = $_SESSION['LSsession']['LSsearch'][$LSobject]['orderby'];
            $ordersense = $_SESSION['LSsession']['LSsearch'][$LSobject]['ordersense'];
            $doSubDn = $_SESSION['LSsession']['LSsearch'][$LSobject]['doSubDn'];
          }
          else {
            $filter = NULL;
            $topDn = $object -> config['container_dn'].','.$GLOBALS['LSsession'] -> topDn;
            $params = array('scope' => 'one');
            $pattern = false;
            $recur = false;
            $approx = false;
            $orderby = false;
            $ordersense = 'ASC';
            $subDnLdapServer = $GLOBALS['LSsession'] -> getSubDnLdapServer();
            $doSubDn = (($subDnLdapServer)&&(!$GLOBALS['LSsession']->isSubDnLSobject($LSobject)));
          }
          
          if (isset($_REQUEST['LSview_search_submit'])) {
            if (isset($_REQUEST['LSview_pattern']) && ($_REQUEST['LSview_pattern']!=$pattern)) {
              $pattern = $_REQUEST['LSview_pattern'];
            }

            $approx = (isset($_REQUEST['LSview_approx']));
            
            if ($pattern && $pattern!='') {
              $filter='(|';
              if ($approx) {
                foreach ($object -> attrs as $attr_name => $attr_val) {
                  $filter.='('.$attr_name.'~='.$pattern.')';
                }
              }
              else {
                foreach ($object -> attrs as $attr_name => $attr_val) {
                  $filter.='('.$attr_name.'=*'.$pattern.'*)';
                }
              }
              $filter.=')';
            }
            else {
              $filter = NULL;
            }
            
            if (isset($_REQUEST['LSview_recur'])) {
              $recur = true;
              $params['scope'] = 'sub';
              $topDn = $GLOBALS['LSsession'] -> topDn;
            }
            else {
              $recur = false;
              $params['scope'] = 'one';
              $topDn = $object -> config['container_dn'].','.$GLOBALS['LSsession'] -> topDn;
            }
          }
          
          $sort=false;
          if ((isset($_REQUEST['orderby']))) {
            $possible_values= array('displayValue','subDn');
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
          
          if (($GLOBALS['LSsession'] -> cacheSearch()) && isset($_SESSION['LSsession']['LSsearch'][$hash]) && (!isset($_REQUEST['refresh']))) {
            // On affiche à partir du cache
            $searchData=$_SESSION['LSsession']['LSsearch'][$hash];
            LSdebug('Recherche : From cache');
          }
          else {
            LSdebug('Recherche : Load');
            if ($GLOBALS['LSsession'] -> canCreate($LSobject)) {
              $LSview_actions[] = array (
                'label' => _('Nouveau'),
                'url' => 'create.php?LSobject='.$LSobject,
                'action' => 'create'
              );
              $canCopy=true;
            }
            $LSview_actions[] = array (
              'label' => _('Rafraîchir'),
              'url' => 'view.php?LSobject='.$LSobject.'&amp;refresh',
              'action' => 'refresh'
            );
            
            $list=$object -> listObjects($filter,$topDn,$params);
            

            $nbObjects=0;
            foreach($list as $thisObject) {
              if ($GLOBALS['LSsession'] -> canAccess($LSobject,$thisObject->getValue('dn'))) {
                $subDn_name=false;
                if ($doSubDn) {
                  $subDn_name = $thisObject -> getSubDnName();
                }
                $nbObjects++;

                
                $objectList[]=array(
                  'dn' => $thisObject->getValue('dn'),
                  'displayValue' => $thisObject->getDisplayValue(),
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
          }
          
          if ((!isset($searchData['objectList'][0]['actions']))&&(!empty($searchData['objectList']))) {
            LSdebug('Load actions');
            for($i=0;$i<$searchData['LSobject_list_nbresult'];$i++) {
              $actions=array();
              
              $actions[] = array(
                'label' => _('Voir'),
                'url' =>'view.php?LSobject='.$LSobject.'&amp;dn='.$searchData['objectList'][$i]['dn'],
                'action' => 'view'
              );
              
              if ($GLOBALS['LSsession'] -> canEdit($LSobject,$searchData['objectList'][$i]['dn'])) {
                $actions[]=array(
                  'label' => _('Modifier'),
                  'url' => 'modify.php?LSobject='.$LSobject.'&amp;dn='.$searchData['objectList'][$i]['dn'],
                  'action' => 'modify'
                );
              }
              
              if ($canCopy) {
                $actions[] = array(
                  'label' => _('Copier'),
                  'url' =>'create.php?LSobject='.$LSobject.'&amp;load='.$searchData['objectList'][$i]['dn'],
                  'action' => 'copy'
                );
              }
              
              if ($GLOBALS['LSsession'] -> canRemove($LSobject,$searchData['objectList'][$i]['dn'])) {
                $actions[] = array (
                  'label' => _('Supprimer'),
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
              LSdebug('Erreur durant le trie.');
            }
          }
          $GLOBALS['Smarty']->assign('LSobject_list_orderby',$orderby);
          $GLOBALS['Smarty']->assign('LSobject_list_ordersense',$ordersense);
          
          if ($GLOBALS['LSsession'] -> cacheSearch()) {
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
          }
          
          $GLOBALS['LSsession'] -> addJSscript('LSview.js');
          
          $GLOBALS['Smarty']->assign('LSview_search',array(
            'action' => $_SERVER['PHP_SELF'],
            'submit' => _('Rechercher'),
            'LSobject' => $LSobject
          ));
          
          $GLOBALS['Smarty']->assign('LSview_search_recur_label',_('Recherche récursive'));
          $GLOBALS['Smarty']->assign('LSview_search_approx_label',_('Recherche approximative'));

          $GLOBALS['Smarty']->assign('LSobject_list_without_result_label',_("Cette recherche n'a retourné aucun résultat."));
          $GLOBALS['Smarty']->assign('_Actions',_('Actions'));
          $GLOBALS['Smarty']->assign('_Modifier',_('Modifier'));
          $GLOBALS['Smarty']->assign('LSobject_list',$searchData['objectList']);
          $GLOBALS['Smarty']->assign('LSobject_list_objecttype',$LSobject);
          $GLOBALS['Smarty'] -> assign('LSview_actions',$searchData['LSview_actions']);
          $GLOBALS['LSsession'] -> setTemplate('viewList.tpl');
        }
      }
      else {
        $GLOBALS['LSerror'] -> addErrorCode(1004,$LSobject);
      }
    }
    else {
      $GLOBALS['LSerror'] -> addErrorCode(1011);
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
