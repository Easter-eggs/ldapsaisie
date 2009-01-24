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

require_once 'includes/class/class.LSsession.php';

if(LSsession :: startLSsession()) {
  if (isset($_REQUEST['LSobject'])) {
    $LSobject = $_REQUEST['LSobject'];
    
    if ( LSsession ::loadLSobject($LSobject) ) {
      $objectList=array();
      $object = new $LSobject();
      
      $GLOBALS['Smarty']->assign('pagetitle',$object -> getLabel());
      $GLOBALS['Smarty']->assign('LSobject_list_objectname',$object -> getLabel());
      
      if (isset($_SESSION['LSsession']['LSsearch'][$LSobject])) {
        $filter = $_SESSION['LSsession']['LSsearch'][$LSobject]['filter'];
        if (isCompatibleDNs($_SESSION['LSsession']['LSsearch'][$LSobject]['topDn'],LSsession :: getTopDn())) {
          $topDn = $_SESSION['LSsession']['LSsearch'][$LSobject]['topDn'];
          if (isset($_SESSION['LSsession']['LSsearch'][$LSobject]['selectedTopDn'])) {
            $selectedTopDn = $_SESSION['LSsession']['LSsearch'][$LSobject]['selectedTopDn'];
          }
          else {
            $selectedTopDn = LSsession :: getTopDn();
          }
        }
        else {
          $selectedTopDn  = LSsession :: getTopDn();
          $topDn = $object -> config['container_dn'].','.LSsession :: getTopDn();
        }
        $params = $_SESSION['LSsession']['LSsearch'][$LSobject]['params'];
        $pattern = $_SESSION['LSsession']['LSsearch'][$LSobject]['pattern'];
        $recur = $_SESSION['LSsession']['LSsearch'][$LSobject]['recur'];
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
        $selectedTopDn = LSsession :: getTopDn();
        $orderby = false;
        $_REQUEST['orderby']=$GLOBALS['LSobjects'][$LSobject]['orderby'];
        $ordersense = 'ASC';
        $subDnLdapServer = LSsession :: getSubDnLdapServer();
        $doSubDn = (($subDnLdapServer)&&(!LSsession :: isSubDnLSobject($LSobject)));
      }
      
      if (isset($_REQUEST['LSview_search_submit'])) {
        if ( $_REQUEST['LSview_pattern']!=$pattern ) {
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
          if (LSsession :: validSubDnLdapServer($_REQUEST['LSselect_topDn'])) {
            $topDn = $_REQUEST['LSselect_topDn'];
            $selectedTopDn = $topDn;
          }
          else {
            $topDn = LSsession :: getTopDn();
            $selectedTopDn = $topDn;
          }
        }
        else {
          $recur = false;
          $params['scope'] = 'one';
          if (LSsession :: validSubDnLdapServer($_REQUEST['LSselect_topDn'])) {
            $topDn = $object -> config['container_dn'].','.$_REQUEST['LSselect_topDn'];
            $selectedTopDn = $_REQUEST['LSselect_topDn'];
          }
          else {
            $topDn = $object -> config['container_dn'].','.LSsession :: getTopDn();
            $selectedTopDn = LSsession :: getTopDn();
          }
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
        'selectedTopDn' => $selectedTopDn,
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
      $GLOBALS['Smarty']->assign('LSselect_topDn',$selectedTopDn);
      
      $multiple = (isset($_REQUEST['multiple']))?1:0;
      
      // Hidden fields
      $GLOBALS['Smarty']->assign('LSview_search_hidden_fields',array(
        'LSobject' => $LSobject,
        'LSview_search_submit' => 1,
        'ajax' => 1,
        'multiple' => $multiple
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
        LSdebug('From cache');
      }
      else {
        LSdebug('Load');
        $LSview_actions[] = array (
          'label' => _('Rafraîchir'),
          'url' => 'view.php?LSobject='.$LSobject.'&amp;refresh',
          'action' => 'refresh'
        );
        
        $list=$object -> listObjectsName($filter,$topDn,$params);
        $nbObjects=count($list);
        $searchData['LSobject_list_nbresult']=$nbObjects;

        $c=0;
        
        foreach($list as $objDn => $objName) {
          if (LSsession :: canAccess($LSobject,$objDn)) {

            $c++;
            unset($actions);
            
            $subDn_name=false;
            if ($doSubDn) {
              $subDn_name = $object -> getSubDnName($objDn);
            }
            
            $objectList[]=array(
              'dn' => $objDn,
              'displayName' => $objName,
              'subDn' => $subDn_name
            );
          }
          else {
            LSdebug($dn);
          }
        }
        $searchData['objectList']=$objectList;
        $searchData['LSview_actions'] = $LSview_actions;
        if (LSsession :: cacheSearch()) {
          $_SESSION['LSsession']['LSsearch'][$hash]=$searchData;
        }
        if ($orderby) {
          $sort=true;
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
        $_SESSION['LSsession']['LSsearch'][$hash]=$searchData;
      }
      $GLOBALS['Smarty']->assign('LSobject_list_orderby',$orderby);
      $GLOBALS['Smarty']->assign('LSobject_list_ordersense',$ordersense);
      
      // Pagination
      if ($searchData['LSobject_list_nbresult'] > NB_LSOBJECT_LIST_SELECT) {
        if (isset($_REQUEST['page'])) {
          $searchData['objectList'] = array_slice($searchData['objectList'], ($_REQUEST['page']) * NB_LSOBJECT_LIST_SELECT, NB_LSOBJECT_LIST_SELECT);
          $GLOBALS['Smarty']->assign('LSobject_list_currentpage',$_REQUEST['page']);
          
        }
        else {
          $searchData['objectList'] = array_slice($searchData['objectList'], 0, NB_LSOBJECT_LIST_SELECT);
          $GLOBALS['Smarty']->assign('LSobject_list_currentpage',0);
        }
        $searchData['LSobject_list_nbpage']=ceil($searchData['LSobject_list_nbresult'] / NB_LSOBJECT_LIST_SELECT);
        $GLOBALS['Smarty']->assign('LSobject_list_nbpage',$searchData['LSobject_list_nbpage']);
      }
      
      // Select/Pas Select
      for($i=0;$i<count($searchData['objectList']);$i++) {
        if (is_array($_SESSION['LSselect'][$LSobject])) {
          if(in_array($searchData['objectList'][$i]['dn'],$_SESSION['LSselect'][$LSobject])) {
            $select = true;
          }
          else {
            $select = false;
          }
        }
        else {
          $select = false;
        }
        $searchData['objectList'][$i]['select']=$select;
      }        
      
      LSsession :: addJSscript('LSview.js');
      
      $GLOBALS['Smarty']->assign('LSview_search',array(
        'action' => $_SERVER['PHP_SELF'],
        'submit' => _('Rechercher'),
        'LSobject' => $LSobject
      ));
      
      $GLOBALS['Smarty']->assign('LSview_search_recur_label',_('Recherche récursive'));
      $GLOBALS['Smarty']->assign('LSview_search_approx_label',_('Recherche approximative'));

      $GLOBALS['Smarty']->assign('LSobject_list_without_result_label',_("Cette recherche n'a retourné aucun résultat."));
      $GLOBALS['Smarty']->assign('LSobject_list',$searchData['objectList']);
      $GLOBALS['Smarty']->assign('LSobject_list_objecttype',$LSobject);
      $GLOBALS['Smarty'] -> assign('LSview_actions',$searchData['LSview_actions']);
      $GLOBALS['Smarty'] -> assign('LSselect_multiple',$multiple);
      if (isset($_REQUEST['ajax'])) {
        LSsession :: setTemplate('select_table.tpl');
      }
      else {
        LSsession :: setTemplate('select.tpl');
      }
      LSsession :: setAjaxDisplay();
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
