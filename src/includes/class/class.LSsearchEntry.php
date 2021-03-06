<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * https://ldapsaisie.org
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

LSsession :: loadLSclass('LSlog_staticLoggerClass');

/**
 * Object LSsearchEntry
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSsearchEntry extends LSlog_staticLoggerClass {

  // The LSsearch object
  private $LSsearch=NULL;

  // The LdapObject type of search
  private $LSobject=NULL;

  // DN
  private $dn;

  // The parameters of the search
  private $params = array();

  // The attributes list
  private $attrs_list=array();

  // The attributes values
  private $attrs=array();

  // Cache
  private $cache=array();

  // Other values
  private $other_values=array();

  /**
   * Constructor
   *
   * @param[in] $LSobject string The LdapObject type of search
   * @param[in] $params array Parameters of search
   * @param[in] $resultEntry array The data of the result entry
   *
   **/
  public function __construct(&$LSsearch, $LSobject, $params, &$result, $id) {
    if (!LSsession :: loadLSobject($LSobject)) {
      return;
    }
    $this -> LSsearch =& $LSsearch;
    $this -> LSobject = $LSobject;
    $this -> params = $params;
    $this -> id = $id;
    $this -> dn =& $result[$id]['dn'];
    $this -> attrs_list = $LSsearch -> getAttributes();
    $this -> attrs =& $result[$id]['attrs'];
    $this -> cache =& $result[$id]['cache'];
  }

  /**
   * Allow conversion of LSsearchEntry to string
   *
   * @retval string The string representation of the LSsearchEntry
   */
  public function __toString() {
    return $this -> LSsearch." -> <LSsearchEntry of ".$this -> dn." (ID #".$this -> id.")>";
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
    elseif (array_key_exists($key,$this->other_values)) {
      return $this->other_values[$key];
    }
    elseif ($key=='subDn' || $key=='subDnName') {
      return $this -> subDn;
    }
    elseif ($key=='dn') {
      return $this -> dn;
    }
  }

  /**
   * Add value in array $this -> other_values
   *
   * @param[in] $name string The value name
   * @param[in] $value mixed The value
   *
   * @retval void
   **/
  public function registerOtherValue($name,$value) {
    $this -> other_values[$name]=$value;
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
    elseif ($key=='LSobject'||$key=='type_name'||$key=='type') {
      return $this -> LSobject;
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
          'url' => 'object/'.$this -> LSobject.'/'.urlencode($this -> dn),
          'action' => 'view'
        )
      );

      if (LSsession :: canEdit($this -> LSobject,$this -> dn)) {
        $this -> cache['actions'][]=array(
          'label' => _('Modify'),
          'url' => 'object/'.$this -> LSobject.'/'.urlencode($this -> dn).'/modify',
          'action' => 'modify'
        );
      }

      if ($this -> LSsearch -> canCopy) {
        $this -> cache['actions'][] = array(
          'label' => _('Copy'),
          'url' => 'object/'.$this -> LSobject.'/create?load='.urlencode($this -> dn),
          'action' => 'copy'
        );
      }

      if (LSsession :: canRemove($this -> LSobject,$this -> dn)) {
        $this -> cache['actions'][] = array (
          'label' => _('Delete'),
          'url' => 'object/'.$this -> LSobject.'/'.urlencode($this -> dn).'/remove',
          'action' => 'delete'
        );
      }
      $this -> LSsearch -> addResultToCache();
      return $this -> cache['actions'];
    }
    elseif (is_array($this->LSsearch->extraDisplayedColumns) && array_key_exists($key,$this->LSsearch->extraDisplayedColumns)) {
      if(isset($this -> cache[$key])) {
        return $this -> cache[$key];
      }
      if (isset($this->LSsearch->extraDisplayedColumns[$key]['generateFunction'])) {
        if (!is_callable($this->LSsearch->extraDisplayedColumns[$key]['generateFunction']))
          return False;
        $ret=call_user_func_array($this->LSsearch->extraDisplayedColumns[$key]['generateFunction'],array(&$this));
      }
      else {
        $ret=$this -> getFData($this->LSsearch->extraDisplayedColumns[$key]['LSformat']);
        if (empty($ret) && is_array($this->LSsearch->extraDisplayedColumns[$key]['alternativeLSformats'])) {
          foreach($this->LSsearch->extraDisplayedColumns[$key]['alternativeLSformats'] as $format) {
            $ret=$this -> getFData($format);
            if (!empty($ret)) break;
          }
        }
        if (!empty($ret) && isset($this->LSsearch->extraDisplayedColumns[$key]['formaterLSformat'])) {
          $this -> registerOtherValue('val',$ret);
          $ret=$this -> getFData($this->LSsearch->extraDisplayedColumns[$key]['formaterLSformat']);
        }
        if (!empty($ret) && isset($this->LSsearch->extraDisplayedColumns[$key]['formaterFunction'])) {
          if (is_callable($this->LSsearch->extraDisplayedColumns[$key]['formaterFunction'])) {
            $ret=call_user_func($this->LSsearch->extraDisplayedColumns[$key]['formaterFunction'],$ret);
          }
          else {
            $func=$this->LSsearch->extraDisplayedColumns[$key]['formaterFunction'];
            if(is_array($func)) $func=print_r($func,1);
            LSerror::addErrorCode('LSsearchEntry_01',array('func' => $func, 'column' => $key));
          }
        }
      }
      $this -> cache[$key] = $ret;
      return $ret;
    }
    elseif (in_array($key, $this -> attrs_list)) {
      return (isset($this -> attrs[$key])?$this -> attrs[$key]:null);
    }
    elseif (array_key_exists($key,$this->params['customInfos'])) {
      $cache = $this -> getConfig("customInfos.$key.cache", true, 'bool');
      if($cache && isset($this -> cache['customInfos'][$key])) {
        self :: log_debug("__get($key): custom info retrieved from cache");
        return $this -> cache['customInfos'][$key];
      }
      if(is_array($this->params['customInfos'][$key]['function']) && is_string($this->params['customInfos'][$key]['function'][0])) {
        self :: log_debug("__get($key): load class '".$this->params['customInfos'][$key]['function'][0]."'");
        LSsession::loadLSclass($this->params['customInfos'][$key]['function'][0]);
      }
      if(is_callable($this->params['customInfos'][$key]['function'])) {
        self :: log_debug("__get($key): call ".varDump($this->params['customInfos'][$key]['function'])."");
        $value = call_user_func($this->params['customInfos'][$key]['function'], $this, $this->params['customInfos'][$key]['args']);
        if ($cache)
          $this -> cache['customInfos'][$key] = $value;
        return $value;
      }
      else
        self :: log_error("__get($key): custom info function is not callable: ".varDump($this->params['customInfos'][$key]['function']));
    }
    // Unknown key, log warning
    self :: log_warning("$this -> __get($key): invalid property requested\n".LSlog :: get_debug_backtrace_context());
    return __("Unknown property !");
  }

  /**
   * Return a configuration parameter (or default value)
   *
   * @param[] $param      The configuration parameter
   * @param[] $default    The default value (default : null)
   * @param[] $cast       Cast resulting value in specific type (default : disabled)
   *
   * @retval mixed The configuration parameter value or default value if not set
   **/
  function getConfig($param, $default=null, $cast=null) {
    return LSconfig :: get($param, $default, $cast, $this -> params);
  }

}

/**
 * Error Codes
 **/
LSerror :: defineError('LSsearchEntry_01',
___("LSsearchEntry : Invalid formaterFunction %{func} for extraDisplayedColumns %{column}.")
);
