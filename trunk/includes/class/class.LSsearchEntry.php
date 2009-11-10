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

/**
 * Object LSsearchEntry
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSsearchEntry { 
  
  // The LSsearch object
  private $LSsearch=NULL;
  
  // The LdapObject type of search
  private $LSobject=NULL;
  
  // DN
  private $dn;
  
  // The parameters of the search
  private $params=array ();
  
  // The hash of the search parameters
  private $hash = NULL;
  
  // The attributes values
  private $attrs=array();
  
  // Cache
  private $cache=array();
  
  /**
   * Constructor
   * 
   * @param[in] $LSobject string The LdapObject type of search
   * @param[in] $params array Parameters of search
   * @param[in] $hash array Parameters of search
   * @param[in] $resultEntry array The data of the result entry
   * 
   **/
  function LSsearchEntry(&$LSsearch,$LSobject,$params,$hash,&$result,$id) {
    if (!LSsession :: loadLSobject($LSobject)) {
      return;
    }
    $this -> LSsearch = $LSsearch;
    $this -> LSobject = $LSobject;
    $this -> params = $params;
    $this -> id = $id;
    $this -> dn =& $result[$id]['dn'];
    $this -> attrs =& $result[$id]['attrs'];
    $this -> cache =& $result[$id]['cache'];
  }
  
  /**
   * Get text value of entry
   * 
   * @param[in] $key string The name of the value
   * 
   * @retval mixed The value
   **/
  public function get($key) {
    if (in_array($key,array_keys($this -> attrs))) {
      return $this -> attrs[$key];
    }
    elseif ($key=='subDn' || $key=='subDnName') {
      return $this -> subDn;
    }
  }

  /**
   * Get formated text value of entry
   * 
   * @param[in] $format string The format of the value
   * 
   * @retval mixed The formated value
   **/  
  public function getFData($format) {
    return getFData($format,$this,'get');
  }
  
  
  /**
   * Access to infos of the entry
   * 
   * @param[in] $key string The name of the value
   * 
   * @retval mixed The value
   **/
  public function __get($key) {
    if ($key=='displayName') {
      if (isset($this -> cache['displayName'])) {
        return $this -> cache['displayName'];
      }
      $this -> cache['displayName'] = $this -> getFData($this -> params['displayFormat']);
      return $this -> cache['displayName'];
    }
    elseif ($key=='dn') {
      return $this -> dn;
    }
    elseif ($key=='subDn' || $key=='subDnName') {
      if ($this -> cache['subDn']) {
        return $this -> cache['subDn'];
      }
      if ($this -> LSsearch -> displaySubDn) {
        $this -> cache['subDn'] = LSldapObject::getSubDnName($this -> dn);
        return $this -> cache['subDn'];
      }
    }
    elseif ($key=='actions') {
      if (isset($this -> cache['actions'])) {
        return $this -> cache['actions'];
      }
      $this -> cache['actions'] = array (
        array(
          'label' => _('View'),
          'url' =>'view.php?LSobject='.$this -> LSobject.'&amp;dn='.$this -> dn,
          'action' => 'view'
        )
      );
      
      if (LSsession :: canEdit($this -> LSobject,$this -> dn)) {
        $this -> cache['actions'][]=array(
          'label' => _('Modify'),
          'url' => 'modify.php?LSobject='.$this -> LSobject.'&amp;dn='.$this -> dn,
          'action' => 'modify'
        );
      }
      
      if ($this -> LSsearch -> canCopy) {
        $this -> cache['actions'][] = array(
          'label' => _('Copy'),
          'url' =>'create.php?LSobject='.$this -> LSobject.'&amp;load='.$this -> dn,
          'action' => 'copy'
        );
      }
      
      if (LSsession :: canRemove($this -> LSobject,$this -> dn)) {
        $this -> cache['actions'][] = array (
          'label' => _('Delete'),
          'url' => 'remove.php?LSobject='.$this -> LSobject.'&amp;dn='.$this -> dn,
          'action' => 'delete'
        );
      }
      $this -> LSsearch -> addResultToCache();
      return $this -> cache['actions'];
    }
    elseif ($key=='LSselect') {
      if (is_array($_SESSION['LSselect'][$this -> LSobject])) {
        if(in_array($this -> dn,$_SESSION['LSselect'][$this -> LSobject])) {
          return true;
        }
      }
      return;
    }
    elseif (in_array($key,array_keys($this -> attrs))) {
      return $this -> attrs[$key];
    }
    else {
      return __("Unknown property !");
    }
  }

}

?>