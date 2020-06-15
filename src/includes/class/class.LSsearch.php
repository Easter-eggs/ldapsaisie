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
 * Object LSsearch
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSsearch {

  // The LdapObject type of search
  private $LSobject=NULL;

  // The configuration of search
  private $config;

  // The context of search
  private $context;

  // The parameters of the search
  private $params=array (
    // Search params
    'filter' => NULL,
    'pattern' => NULL,
    'predefinedFilter' => false,
    'basedn' => NULL,
    'subDn' => NULL,
    'scope' => NULL,
    'sizelimit' => 0,
    'attronly' => false,    // If true, only attribute names are returned
    'approx' => false,
    'recursive' => false,
    'attributes' => array(),
    // Display params
    'onlyAccessible' => NULL,
    'sortDirection' => NULL,
    'sortBy' => NULL,
    'sortlimit' => 0,
    'displaySubDn' => NULL,
    'displayFormat' => NULL,
    'nbObjectsByPage' => NB_LSOBJECT_LIST,
    'nbObjectsByPageChoices' => NULL,
    'nbPageLinkByPage' => 10,
    'customInfos' => array(),
    'withoutCache' => false,
    'extraDisplayedColumns' => false,
  );

  // The cache of search parameters
  private $_searchParams = NULL;

  // The cache of the hash of the search parameters
  private $_hash = NULL;

  // The result of the search
  private $result=NULL;

  // Caches
  private $_canCopy=NULL;

  // Logger
  private $logger = null;

  /**
   * Constructor
   *
   * @param[in] $LSobject string The LdapObject type of search
   * @param[in] $context string Context of search (LSrelation / LSldapObject/ ...)
   * @param[in] $params array Parameters of search
   * @param[in] $purgeParams boolean If params in session have to be purged
   *
   **/
  public function __construct($LSobject,$context,$params=null,$purgeParams=false) {
    $this -> logger = LSlog :: get_logger(get_called_class());
    if (!LSsession :: loadLSobject($LSobject)) {
      return;
    }
    $this -> LSobject = $LSobject;

    $this -> loadConfig();

    if (isset($_REQUEST['LSsearchPurgeSession'])) {
      $this -> purgeSession();
    }

    $this -> context = $context;

    if (!$purgeParams) {
      if (! $this -> loadParamsFromSession()) {
        $this -> logger -> debug('LSsearch : load default parameters');
        $this -> loadDefaultParameters();
      }
    }
    else {
      $this -> purgeParams($LSobject);
      $this -> loadDefaultParameters();
    }

    if (is_array($params)) {
      $this -> setParams($params);
    }

  }

  /**
   * Load configuration from LSconfig
   *
   * @retval void
   */
  private function loadConfig() {
    $this -> config = LSconfig::get("LSobjects.".$this -> LSobject.".LSsearch");
    if (isset($this -> config['predefinedFilters']) && is_array($this -> config['predefinedFilters'])) {
      foreach($this -> config['predefinedFilters'] as $filter => $label) {
        if(!LSldap::isValidFilter($filter)) {
          LSerror::addErrorCode('LSsearch_15',array('label' => $label, 'filter' => $filter, 'type' => $this -> LSobject));
          unset($this -> config['predefinedFilters'][$key]);
        }
      }
    }
  }

  /**
   * Load default search parameters from configuration
   *
   * @retval boolean True on success or False
   */
  private function loadDefaultParameters() {
    if (isset($this -> config['params']) && is_array($this -> config['params'])) {
      return $this -> setParams($this -> config['params']);
    }
    return true;
  }

  /**
   * Load search parameters from session
   *
   * @retval boolean True if params has been loaded from session or False
   */
  private function loadParamsFromSession() {
    $this -> logger -> debug('LSsearch : load context params session '.$this -> context);
    if (isset($_SESSION['LSsession']['LSsearch'][$this -> LSobject]['params'][$this -> context]) && is_array($_SESSION['LSsession']['LSsearch'][$this -> LSobject]['params'][$this -> context])) {
      $params = $_SESSION['LSsession']['LSsearch'][$this -> LSobject]['params'][$this -> context];

      if ($params['filter']) {
        $params['filter'] = Net_LDAP2_Filter::parse($params['filter']);
      }

      $this -> params = $params;
      return true;
    }
    return;
  }

  /**
   * Save search parameters in session
   *
   * @retval void
   */
  private function saveParamsInSession() {
    $this -> logger -> debug('LSsearch : save context params session '.$this -> context);
    $params = $this -> params;
    if ($params['filter'] instanceof Net_LDAP2_Filter) {
      $params['filter'] = $params['filter'] -> asString();
    }

    foreach ($params as $param => $value) {
      if ( !isset($_SESSION['LSsession']['LSsearch'][$this -> LSobject]['params'][$this -> context][$param]) || $_SESSION['LSsession']['LSsearch'][$this -> LSobject]['params'][$this -> context][$param]!=$value) {
        $this -> logger -> debug("S: $param => ".varDump($value));
        $_SESSION['LSsession']['LSsearch'][$this -> LSobject]['params'][$this -> context][$param]=$value;
      }
    }
  }

  /**
   * Purge parameters in session
   *
   * @param[in] $LSobject string The LSobject type
   *
   * @retval void
   */
  public static function purgeParams($LSobject) {
    unset($_SESSION['LSsession']['LSsearch'][$LSobject]['params']);
  }

  /**
   * Purge cache
   *
   * @retval void
   */
  public static function purgeCache($LSobject) {
    unset($_SESSION['LSsession']['LSsearch'][$LSobject]);
  }

  /**
   * Purge session
   *
   * @retval void
   */
  private function purgeSession() {
    unset($_SESSION['LSsession']['LSsearch']);
  }

  /**
   * Define one search parameter
   *
   * @param[in] $param string The parameter name
   * @param[in] $value mixed The parameter value
   *
   * @retval boolean True on success or False
   */
  public function setParam($param,$value) {
    return $this -> setParams(array($param => $value));
  }

  /**
   * Define search parameters
   *
   * @param[in] $params array Parameters of search
   *
   * @retval boolean True on success or False
   */
  public function setParams($params) {
    $OK=true;

    // Filter
    if (isset($params['filter'])) {
      if (is_string($params['filter'])) {
        $filter = Net_LDAP2_Filter::parse($params['filter']);
        if (!LSerror::isLdapError($filter)) {
          $this -> params['filter'] = $filter;
        }
        else {
          LSerror :: addErrorCode('LSsearch_01',$params['filter']);
          $OK=false;
        }
      }
      elseif($params['filter'] instanceof Net_LDAP2_Filter) {
        $this -> params['filter'] =& $params['filter'];
      }
    }

    // Approx
    if (isset($params['approx'])) {
      if (is_bool($params['approx']) || $params['approx']==0 || $params['approx']==1) {
        $this -> params['approx'] = (bool)$params['approx'];
      }
      else {
        LSerror :: addErrorCode('LSsearch_05','approx');
        $OK=false;
      }
    }

    // Without Cache
    if (isset($params['withoutCache'])) {
      if (is_bool($params['withoutCache']) || $params['withoutCache']==0 || $params['withoutCache']==1) {
        $this -> params['withoutCache'] = (bool)$params['withoutCache'];
      }
      else {
        LSerror :: addErrorCode('LSsearch_05','withoutCache');
        $OK=false;
      }
    }

    // Patterm
    if (isset($params['pattern'])) {
      if ($params['pattern']=="") {
        $this -> params['pattern'] = NULL;
      }
      elseif ($this -> isValidPattern($params['pattern'])) {
        $this -> params['pattern'] = $params['pattern'];
      }
    }


    // BaseDN
    if (isset($params['basedn']) && is_string($params['basedn'])) {
      if (isCompatibleDNs(LSsession :: getRootDn(),$params['basedn'])) {
        $this -> params['basedn'] = $params['basedn'];
      }
      else {
        LSerror :: addErrorCode('LSsearch_02',$params['basedn']);
        $OK=false;
      }
    }

    // subDn
    if (isset($params['subDn']) && is_string($params['subDn'])) {
      if (LSsession :: validSubDnLdapServer($params['subDn'])) {
        $this -> params['subDn'] = $params['subDn'];
      }
      else {
        LSerror :: addErrorCode('LSsearch_03','subDn');
        $OK=false;
      }
    }

    // Scope
    if (isset($params['scope']) && is_string($params['scope'])) {
      if (in_array($params['scope'],array('sub','one','base'))) {
        $this -> params['scope'] = $params['scope'];
      }
      else {
        LSerror :: addErrorCode('LSsearch_03','scope');
        $OK=false;
      }
    }

    // nbObjectsByPage
    if (isset($params['nbObjectsByPage'])) {
      if (((int)$params['nbObjectsByPage'])>1 ) {
        $this -> params['nbObjectsByPage'] = (int)$params['nbObjectsByPage'];
      }
      else {
        LSerror :: addErrorCode('LSsearch_03','nbObjectsByPage');
        $OK=false;
      }
    }

    // nbObjectsByPageChoices
    if (isset($params['nbObjectsByPageChoices'])) {
      if (is_array($params['nbObjectsByPageChoices'])) {
        $choices = array();
        $choiceError = false;
        foreach($params['nbObjectsByPageChoices'] as $choice) {
          if (is_int($choice) && !in_array($choice, $choices)) {
            $choices[] = $choice;
          }
          else {
            $choiceError = true;
            break;
          }
        }
        if (!empty($choices) && !$choiceError) {
          $this -> params['nbObjectsByPageChoices'] = $choices;
        }
        else {
          LSerror :: addErrorCode('LSsearch_03','nbObjectsByPageChoices');
          $OK = false;
        }
      }
      else {
        LSerror :: addErrorCode('LSsearch_03','nbObjectsByPageChoices');
        $OK = false;
      }
    }

    // Extra Columns
    if (isset($params['extraDisplayedColumns'])) {
      $this -> params['extraDisplayedColumns']=(bool)$params['extraDisplayedColumns'];
    }

    // Sort Limit
    if (isset($params['sortlimit'])) {
      if (is_int($params['sortlimit']) && $params['sortlimit']>=0 ) {
        $this -> params['sortlimit'] = $params['sortlimit'];
      }
      elseif ((int)$params['sortlimit'] > 0) {
        $this -> params['sortlimit'] = (int)$params['sortlimit'];
      }
      else {
        LSerror :: addErrorCode('LSsearch_03','sortlimit');
        $OK=false;
      }
    }

    // Sort Direction
    if (isset($params['sortDirection']) && is_string($params['sortDirection'])) {
      if (in_array($params['sortDirection'],array('ASC','DESC'))) {
        $this -> params['sortDirection'] = $params['sortDirection'];
      }
      else {
        LSerror :: addErrorCode('LSsearch_03','sortDirection');
        $OK=false;
      }
    }

    // Sort By
    if (isset($params['sortBy']) && is_string($params['sortBy'])) {
      if (in_array($params['sortBy'],array('displayName','subDn')) || ($this ->extraDisplayedColumns && isset($this ->extraDisplayedColumns[$params['sortBy']]))) {
        if ($this -> params['sortBy'] == $params['sortBy']) {
          $this -> toggleSortDirection();
        }
        else {
          $this -> params['sortBy'] = $params['sortBy'];
          if (!isset($params['sortDirection']) || !is_string($params['sortDirection'])) {
            $this -> params['sortDirection'] = 'ASC';
          }
        }
      }
      else {
        LSerror :: addErrorCode('LSsearch_03','sortBy');
        $OK=false;
      }
    }

    // Size Limit
    if (isset($params['sizelimit'])) {
      if (((int)$params['sizelimit']) >= 0) {
        $this -> params['sizelimit'] = $params['sizelimit'];
      }
      else {
        LSerror :: addErrorCode('LSsearch_04');
        $OK=false;
      }
    }

    // Attronly
    if (isset($params['attronly'])) {
      if (is_bool($params['attronly']) || $params['attronly']==0 || $params['attronly']==1) {
        $this -> params['attronly'] = (bool)$params['attronly'];
      }
      else {
        LSerror :: addErrorCode('LSsearch_05','attronly');
        $OK=false;
      }
    }

    // Recursive
    if (isset($params['recursive'])) {
      if (is_bool($params['recursive']) || $params['recursive']==0 || $params['recursive']==1) {
        $this -> params['recursive'] = (bool)$params['recursive'];
      }
      else {
        LSerror :: addErrorCode('LSsearch_05','recursive');
        $OK=false;
      }
    }

    // displaySubDn
    if (isset($params['displaySubDn'])) {
      if (! LSsession :: isSubDnLSobject($this -> LSobject) ) {
        if (is_bool($params['displaySubDn']) || $params['displaySubDn']==0 || $params['displaySubDn']==1) {
          $this -> params['displaySubDn'] = (bool)$params['displaySubDn'];
        }
        else {
          LSerror :: addErrorCode('LSsearch_05','displaySubDn');
          $OK=false;
        }
      }
    }

    // Attributes
    if (isset($params['attributes'])) {
      if (is_string($params['attributes'])) {
        $this -> params['attributes'] = array($params['attributes']);
      }
      elseif (is_array($params['attributes'])) {
        $this -> params['attributes']=array();
        foreach ($params['attributes'] as $attr) {
          if (is_string($attr)) {
            if (LSconfig::get("LSobjects.".$this -> LSobject.".attrs.$attr")) {;
              $this -> params['attributes'][] = $attr;
            }
            else {
              LSerror :: addErrorCode('LSsearch_11',$attr);
            }
          }
        }
      }
      else {
        LSerror :: addErrorCode('LSsearch_06');
        $OK=false;
      }
    }

    // predefinedFilter
    if (isset($params['predefinedFilter'])) {
      if (is_string($params['predefinedFilter'])) {
        if (empty($params['predefinedFilter'])) {
          $this->params['predefinedFilter']=false;
        }
        elseif(is_array($this -> config['predefinedFilters'])) {
          if(isset($this->config['predefinedFilters'][$params['predefinedFilter']])) {
            $this -> params['predefinedFilter'] = $params['predefinedFilter'];
          }
          else {
            LSerror :: addErrorCode('LSsearch_03','predefinedFilter');
            $OK=false;
          }
        }
      }
      else {
        LSerror :: addErrorCode('LSsearch_03','predefinedFilter');
        $OK=false;
      }
    }

    // Display Format
    if (isset($params['displayFormat']) && is_string($params['displayFormat'])) {
      $this -> params['displayFormat'] = $params['displayFormat'];
    }

    // Custom Infos
    if (isset($params['customInfos']) && is_array($params['customInfos'])) {
      foreach($params['customInfos'] as $name => $data) {
        if(is_array($data['function']) && is_string($data['function'][0])) {
          LSsession::loadLSclass($data['function'][0]);
        }
        if (is_callable($data['function'])) {
          $this -> params['customInfos'][$name] = array (
            'function' => &$data['function'],
            'args' => (isset($data['args'])?$data['args']:null),
            'cache' => (isset($data['cache'])?boolval($data['cache']):true),
          );
        }
        else {
          LSerror :: addErrorCode('LSsearch_14',$name);
        }
      }
    }

    // Only Accessible objects
    if (isset($params['onlyAccessible'])) {
      $this -> params['onlyAccessible'] = (bool)$params['onlyAccessible'];
    }

    $this -> saveParamsInSession();
    return $OK;
  }

  /**
   * Return true only if the form is submited
   *
   * @retval boolean True only if the is submited
   **/
  private function formIsSubmited() {
    return isset($_REQUEST['LSsearch_submit']);
  }

  /**
   * Define search parameters by reading Post Data ($_REQUEST)
   *
   * @retval void
   */
  public function setParamsFormPostData() {
    $data = $_REQUEST;

    if (self::formIsSubmited()) {
      // Recursive
      if (is_null($data['recursive'])) {
        $data['recursive']=false;
      }
      else {
        $data['recursive']=true;
      }

      // Approx
      if (is_null($data['approx'])) {
        $data['approx']=false;
      }
      else {
        $data['approx']=true;
      }

      if (isset($data['ajax']) && !isset($data['pattern'])) {
        $data['pattern']="";
      }
    }

    $this -> setParams($data);
  }

  /**
   * Toggle the sort direction
   *
   * @retval void
   **/
  private function toggleSortDirection() {
    if ($this -> params['sortDirection']=="ASC") {
      $this -> params['sortDirection'] = "DESC";
    }
    else {
      $this -> params['sortDirection'] = "ASC";
    }
  }

  /**
   * Make a filter object with a pattern of search
   *
   * @param[in] $pattern The pattern of search. If is null, the pattern in params will be used.
   *
   * @retval mixed Net_LDAP2_Filter on success or False
   */
  public function getFilterFromPattern($pattern=NULL) {
    if ($pattern==NULL) {
      $pattern=$this -> params['pattern'];
    }
    if ($this -> isValidPattern($pattern)) {
      $attrsConfig=LSconfig::get("LSobjects.".$this -> LSobject.".LSsearch.attrs");
      $attrsList=array();
      if (!is_array($attrsConfig)) {
        foreach(LSconfig::get("LSobjects.".$this -> LSobject.".attrs") as $attr => $config) {
          $attrsList[$attr]=array();
        }
      }
      else {
        foreach($attrsConfig as $key => $val) {
          if(is_int($key)) {
            $attrsList[$val]=array();
          }
          else {
            $attrsList[$key]=$val;
          }
        }
      }

      if (empty($attrsList)) {
        LSerror :: addErrorCode('LSsearch_07');
        return;
      }

      $filters=array();
      foreach ($attrsList as $attr => $opts) {
        if ($this -> params['approx']) {
          if (isset($opts['approxLSformat'])) {
            $filter=Net_LDAP2_Filter::parse(getFData($opts['approxLSformat'],array('name'=>$attr,'pattern'=>$pattern)));
          }
          else {
            $filter=Net_LDAP2_Filter::create($attr,'approx',$pattern);
          }
        }
        else {
          if (isset($opts['searchLSformat'])) {
            $filter=Net_LDAP2_Filter::parse(getFData($opts['searchLSformat'],array('name'=>$attr,'pattern'=>$pattern)));
          }
          else {
            $filter=Net_LDAP2_Filter::create($attr,'contains',$pattern);
          }
        }

        if (!Net_LDAP2::isError($filter)) {
          $filters[]=$filter;
        }
        else {
          LSerror :: addErrorCode('LSsearch_08',array('attr' => $attr,'pattern' => $pattern));
          return;
        }
      }
      if(!empty($filters)) {
        $filter=LSldap::combineFilters('or',$filters);
        if ($filter) {
          return $filter;
        }
        else {
          LSerror :: addErrorCode('LSsearch_09');
        }
      }
    }
    else {
      LSerror :: addErrorCode('LSsearch_10');
    }
    return;
  }

  /**
   * Check if search pattern is valid
   *
   * @param[in] $pattern string The pattern
   *
   * @retval boolean True if pattern is valid or False
   **/
  public function isValidPattern($pattern) {
    if (is_string($pattern) && $pattern!= "") {
      $regex = (isset($this -> config['validPatternRegex'])?$this -> config['validPatternRegex']:'/^[\w \-\_\\\'\"^\[\]\(\)\{\}\=\+\£\%\$\€\.\:\;\,\?\/\@]+$/iu');
      if (preg_match($regex, $pattern))
        return True;
    }
    LSerror :: addErrorCode('LSsearch_17');
    return False;
  }

  /**
   * Check if cache is enabled
   *
   * @retval boolean True if cache is enabled or False
   **/
  public function cacheIsEnabled() {
    if (isset($this -> config['cache'])) {
      $conf=$this -> config['cache'];
      if (is_bool($conf) || $conf==0 || $conf==1) {
        return (bool)$conf;
      }
      else {
        LSerror :: addErrorCode('LSsearch_03','cache');
      }
    }
    return LSsession :: cacheSearch();
  }

  /**
   * Methode for parameters value access
   *
   * @param[in] $key string The parameter name
   *
   * @retval mixed The parameter value or NULL
   **/
  public function getParam($key) {
    if(in_array($key,array_keys($this -> params))) {
      if ($key == 'nbObjectsByPageChoices' && !is_array($this -> params['nbObjectsByPageChoices'])) {
        return (isset($GLOBALS['NB_LSOBJECT_LIST_CHOICES']) && is_array($GLOBALS['NB_LSOBJECT_LIST_CHOICES'])?$GLOBALS['NB_LSOBJECT_LIST_CHOICES']:range(NB_LSOBJECT_LIST, NB_LSOBJECT_LIST*4, NB_LSOBJECT_LIST));
}
      return $this -> params[$key];
    }
    return NULL;
  }

  /**
   * Return hidden fileds to add in search form
   *
   * @retval array The hield fields whith their values
   **/
  public function getHiddenFieldForm() {
    return array (
      'LSobject' => $this -> LSobject
    );
  }

  /**
   * Generate an array with search parameters, only parameters whitch have to be
   * passed to Net_LDAP2 for the LDAP search. This array will be store in
   * $this -> _searchParams private variable.
   *
   * @retval void
   **/
  private function generateSearchParams() {
    // Purge the cache of the hash
    $this -> _hash = NULL;

    // Base
    $retval = array(
      'filter' => $this -> params['filter'],
      'basedn' => $this -> params['basedn'],
      'scope' => $this -> params['scope'],
      'sizelimit' => $this -> params['sizelimit'],
      'attronly' => $this -> params['attronly'],
      'attributes' => $this -> params['attributes']
    );

    // Pattern
    if (!is_null($this -> params['pattern'])) {
      $filter=$this ->getFilterFromPattern();
      if (is_null($retval['filter'])) {
        $retval['filter']=$filter;
      }
      else {
        $retval['filter']=LSldap::combineFilters('and',array($retval['filter'],$filter));
      }
    }

    // predefinedFilter
    if (is_string($this -> params['predefinedFilter'])) {
      if (!is_null($retval['filter'])) {
        $filter=LSldap::combineFilters('and',array($this -> params['predefinedFilter'],$retval['filter']));
        if ($filter) {
          $retval['filter']=$filter;
        }
      }
      else {
        $retval['filter']=$this -> params['predefinedFilter'];
      }
    }

    // Filter
    $objFilter=LSldapObject::_getObjectFilter($this -> LSobject);
    if ($objFilter) {
      if (!is_null($retval['filter'])) {
        $filter=LSldap::combineFilters('and',array($objFilter,$retval['filter']));
        if ($filter) {
          $retval['filter']=$filter;
        }
      }
      else {
        $retval['filter']=$objFilter;
      }
    }

    // Recursive
    if (is_null($retval['basedn'])) {
      if (!is_null($this -> params['subDn'])) {
        if ($this -> params['recursive']) {
          $retval['basedn'] = $this -> params['subDn'];
        }
        else {
          $retval['basedn'] = LSconfig::get("LSobjects.".$this -> LSobject.".container_dn").','.$this -> params['subDn'];
        }
      }
      else {
        if ($this -> params['recursive']) {
          $retval['basedn'] = LSsession :: getTopDn();
        }
        else {
          $retval['basedn'] = LSconfig::get("LSobjects.".$this -> LSobject.".container_dn").','.LSsession :: getTopDn();
        }
      }
    }
    if ($this -> params['recursive'] || !isset($retval['scope'])) {
      $retval['scope'] = 'sub';
    }

    if (is_null($this -> params['displayFormat'])) {
      $this -> params['displayFormat']=LSconfig::get("LSobjects.".$this -> LSobject.".display_name_format");
    }

    // Display Format
    $attrs=getFieldInFormat($this -> params['displayFormat']);
    if(is_array($retval['attributes'])) {
      $retval['attributes']=array_merge($attrs,$retval['attributes']);
    }
    else {
      $retval['attributes']=$attrs;
    }

    // Extra Columns
    if ($this -> params['extraDisplayedColumns'] && is_array($this -> config['extraDisplayedColumns'])) {
      foreach ($this -> config['extraDisplayedColumns'] as $id => $conf) {
        $attrs=array();
        if (isset($conf['LSformat'])) {
          $attrs=getFieldInFormat($conf['LSformat']);
          if(is_array($conf['alternativeLSformats'])) {
            foreach ($conf['alternativeLSformats'] as $format) {
              $attrs=array_merge($attrs,getFieldInFormat($format));
            }
          }
          else {
            $attrs=array_merge($attrs,getFieldInFormat($conf['alternativeLSformats']));
          }
          if(isset($conf['formaterLSformat'])) {
            $attrs=array_unique(array_merge($attrs,getFieldInFormat($conf['formaterLSformat'])));
            if(($key = array_search('val', $attrs)) !== false) {
              unset($attrs[$key]);
            }
          }
        }
        if(isset($conf['additionalAttrs'])) {
          $attrs=array_unique(array_merge($attrs,(is_array($conf['additionalAttrs'])?$conf['additionalAttrs']:array($conf['additionalAttrs']))));
        }
        if(is_array($retval['attributes'])) {
          $retval['attributes']=array_merge($attrs,$retval['attributes']);
        }
        else {
          $retval['attributes']=$attrs;
        }
      }
    }

    if (is_array($retval['attributes'])) {
      $retval['attributes']=array_unique($retval['attributes']);
    }

    $this -> _searchParams = $retval;
  }

  /**
   * Get search attributes
   *
   * @retval array The attributes asked in this search
   **/
  public function getAttributes() {
    if (!$this -> _searchParams)
      $this -> generateSearchParams();
    return $this -> _searchParams['attributes'];
  }

  /**
   * Run the search
   *
   * @param[in] $cache boolean Define if the cache can be used
   *
   * @retval boolean True on success or False
   */
  public function run($cache=true) {
    $this -> generateSearchParams();
    if ($this -> _searchParams['filter'] instanceof Net_LDAP2_Filter) {
      $this -> logger -> debug('LSsearch : filter : '.$this -> _searchParams['filter']->asString());
    }
    $this -> logger -> debug('LSsearch : basedn : '.$this -> _searchParams['basedn'].' - scope : '.$this -> _searchParams['scope']);

    if( $cache && (!isset($_REQUEST['refresh'])) && (!$this -> params['withoutCache']) ) {
      $this -> logger -> debug('LSsearch : with the cache');
      $this -> result = $this -> getResultFromCache();
    }
    else {
      $this -> logger -> debug('LSsearch : without the cache');
      $this -> setParam('withoutCache',false);
    }

    if (!$this -> result) {
      $this -> logger -> debug('LSsearch : Not in cache');
      $this -> result=array(
        'sortBy' => NULL,
        'sortDirection' => NULL
      );

      // Search in LDAP
      $list = LSldap :: search(
        $this -> _searchParams['filter'],
        $this -> _searchParams['basedn'],
        $this -> _searchParams
      );

      // Check result
      if ($list === false) {
        LSerror :: addErrorCode('LSsearch_12');
        return;
      }

      if ($this -> getParam('onlyAccessible') && LSsession :: getLSuserObjectDn()) {
        $this -> result['list']=array();

        // Check user rights on objets
        foreach($list as $id => $obj) {
          if (LSsession :: canAccess($this -> LSobject,$obj['dn'])) {
            $this -> result['list'][]=$obj;
          }
        }
      }
      else {
	$this -> result['list']=$list;
      }

      $this -> addResultToCache();
    }

    $this -> doSort();

    return true;
  }

  /**
   * Return an hash corresponding to the parameters of the search
   *
   * @param[in] $searchParams array An optional search params array
   *
   * @retval string The hash of the parameters of the search
   **/
  public function getHash($searchParams=null) {
    if(is_null($searchParams)) {
      $searchParams=$this -> _searchParams;
      if ($this -> _hash) {
        return $this -> _hash;
      }
    }
    if ($searchParams['filter'] instanceof Net_LDAP_Filter) {
      $searchParams['filter']=$searchParams['filter']->asString();
    }
    return hash('md5',print_r($searchParams,true));
  }

  /**
   * Add the result of the search to cache of the session
   *
   * @retval void
   **/
  public function addResultToCache() {
    if ($this -> cacheIsEnabled()) {
      $this -> logger -> debug('LSsearch : Save result in cache.');
      $hash=$this->getHash();
      $_SESSION['LSsession']['LSsearch'][$this -> LSobject][$hash]=$this->result;
    }
  }

  /**
   * Get the result of the search from cache of the session
   *
   * @retval array | False The array of the result of the search or False
   **/
  private function getResultFromCache() {
    if ($this -> cacheIsEnabled()) {
      $hash=$this->getHash();
      if (isset($_SESSION['LSsession']['LSsearch'][$this -> LSobject][$hash])) {
        $this -> logger -> debug('LSsearch : Load result from cache.');
        return $_SESSION['LSsession']['LSsearch'][$this -> LSobject][$hash];
      }
    }
    return;
  }

  /**
   * Get page informations to display
   *
   * @param[in] $page integer The number of the page
   *
   * @retval array The information of the page
   **/
  public function getPage($page=0) {
    if (!LSsession::loadLSclass('LSsearchEntry')) {
      LSerror::addErrorCode('LSsession_05',$this -> LSobject);
      return;
    }
    $page = (int)$page;

    $retval=array(
      'nb' => $page,
      'nbPages' => 1,
      'list' => array(),
      'total' => $this -> total
    );

    if ($retval['total']>0) {
      $this -> logger -> debug('Total : '.$retval['total']);

      if (!$this->params['nbObjectsByPage']) {
        $this->params['nbObjectsByPage']=NB_LSOBJECT_LIST;
      }
      $retval['nbPages']=ceil($retval['total']/$this->params['nbObjectsByPage']);

      $sortTable=$this -> getSortTable();

      $list = array_slice(
        $sortTable,
        ($page * $this->params['nbObjectsByPage']),
        $this->params['nbObjectsByPage']
      );

      foreach ($list as $key => $id) {
        $retval['list'][]=new LSsearchEntry($this,$this -> LSobject,$this -> params,$this -> _hash,$this -> result['list'],$id);
      }
    }
    return $retval;
  }

  /**
   * Get search entries
   *
   * @retval array The entries
   **/
  public function getSearchEntries() {
    if (!LSsession::loadLSclass('LSsearchEntry')) {
      LSerror::addErrorCode('LSsession_05',$this -> LSobject);
      return;
    }
    $retval=array();
    if ($this -> total>0) {
      $sortTable=$this -> getSortTable();

      foreach ($sortTable as $key => $id) {
        $retval[]=new LSsearchEntry($this,$this -> LSobject,$this -> params,$this -> _hash,$this -> result['list'],$id);
      }
    }
    return $retval;
  }

  /**
   * Access to information of this object
   *
   * @param[in] $key string The key of the info
   *
   * @retval mixed The info
   **/
  public function __get($key) {
    $params = array (
      'basedn',
      'sortBy',
      'sortDirection'
    );
    if ($key=='LSobject') {
      return $this -> LSobject;
    }
    elseif (in_array($key,$params)) {
      return $this -> params[$key];
    }
    elseif ($key=='label_objectName') {
      return LSldapObject::getLabel($this -> LSobject);
    }
    elseif ($key=='label_level') {
      return LSsession :: getSubDnLabel();
    }
    elseif ($key=='label_actions') {
      return _('Actions');
    }
    elseif ($key=='label_no_result') {
      return _("This search didn't get any result.");
    }
    elseif ($key=='sort') {
      if (isset($this -> params['sortlimit']) && ($this -> params['sortlimit']>0)) {
        return ($this -> total < $this -> params['sortlimit']);
      }
      return true;
    }
    elseif ($key=='sortlimit') {
      return $this -> params['sortlimit'];
    }
    elseif ($key=='total') {
      return count($this -> result['list']);
    }
    elseif ($key=='label_total') {
      return $this -> total." ".$this -> label_objectName;
    }
    elseif ($key=='displaySubDn') {
      if (LSsession :: subDnIsEnabled()) {
        if (!is_null($this -> params[$key])) {
          return $this -> params[$key];
        }
        else {
          return (! LSsession :: isSubDnLSobject($this -> LSobject) );
        }
      }
      return false;
    }
    elseif ($key=='canCopy') {
      if (!is_null($this -> _canCopy))
        return $this -> _canCopy;
      $this -> _canCopy = LSsession :: canCreate($this -> LSobject);
      return $this -> _canCopy;
    }
    elseif ($key=='predefinedFilters') {
			$retval=array();
			if (is_array($this -> config['predefinedFilters'])) {
				foreach($this -> config['predefinedFilters'] as $filter => $label) {
					$retval[$filter]=__($label);
				}
			}
      return $retval;
    }
    elseif ($key=='extraDisplayedColumns') {
      if ($this->params['extraDisplayedColumns'] && is_array($this -> config['extraDisplayedColumns'])) {
        return $this -> config['extraDisplayedColumns'];
      }
      else {
        return False;
      }
    }
    elseif ($key=='visibleExtraDisplayedColumns') {
      if ($this->params['extraDisplayedColumns'] && is_array($this -> config['extraDisplayedColumns'])) {
        $ret=array();
        foreach($this->config['extraDisplayedColumns'] as $col => $conf) {
          if (isset($conf['visibleTo']) && !LSsession :: isLSprofiles($this -> basedn, $conf['visibleTo'])) {
            continue;
          }
          $ret[$col]=$conf;
        }
        return $ret;
      }
    }
    else {
      throw new Exception('Incorrect property !');
    }
  }

  /**
   * Function use with uasort to sort two entry
   *
   * @param[in] $a array One line of result
   * @param[in] $b array One line of result
   *
   * @retval int Value for uasort
   **/
  private function _sortTwoEntry(&$a,&$b) {
    $sortBy = $this -> params['sortBy'];
    $sortDirection = $this -> params['sortDirection'];
    if ($sortDirection=='ASC') {
      $dir = 1;
    }
    else {
      $dir = -1;
    }
    $oa = new LSsearchEntry($this,$this -> LSobject,$this -> params,$this -> _hash,$this -> result['list'],$a);
    $va = $oa->$sortBy;
    $ob = new LSsearchEntry($this,$this -> LSobject,$this -> params,$this -> _hash,$this -> result['list'],$b);
    $vb = $ob->$sortBy;

    if ($va == $vb) return 0;

    $val = strnatcmp(strtolower($va), strtolower($vb));
    return $val*$dir;
  }

  /**
   * Function to run after using the result. It's update the cache
   *
   * IT'S FUNCTION IS VERY IMPORTANT !!!
   *
   * @retval void
   **/
  public function afterUsingResult() {
    $this -> addResultToCache();
  }

  /**
   * Redirect user to object view if the search have only one result
   *
   * @retval boolean True only if user have been redirected
   **/
  public function redirectWhenOnlyOneResult() {
    if ($this -> total == 1 && $this -> result && self::formIsSubmited()) {
      LSurl :: redirect('object/'.$this -> LSobject.'/'.urlencode($this -> result['list'][0]['dn']));
    }
    return;
  }

  /**
   * Run the sort if it's enabled and if the result is not in the cache
   *
   * @retval boolean True on success or false
   **/
  private function doSort() {
    if (!$this -> sort) {
      $this -> logger -> debug('doSort : sort is disabled');
      return true;
    }
    if (is_null($this -> params['sortBy'])) {
      return;
    }
    if (is_null($this -> params['sortDirection'])) {
      $this -> params['sortDirection']='ASC';
    }

    if ($this->total==0) {
      return true;
    }

    if (isset($this -> result['sort'][$this -> params['sortBy']][$this -> params['sortDirection']])) {
      $this -> logger -> debug('doSort : from cache');
      return true;
    }

    $this -> logger -> debug('doSort : '.$this -> params['sortBy'].' - '.$this -> params['sortDirection']);

    $this -> result['sort'][$this -> params['sortBy']][$this -> params['sortDirection']]=range(0,($this -> total-1));

    if (!LSsession :: loadLSClass('LSsearchEntry')) {
      LSerror::addErrorCode('LSsession_05','LSsearchEntry');
      return;
    }

    if (!uasort(
      $this -> result['sort'][$this -> params['sortBy']][$this -> params['sortDirection']],
      array($this,'_sortTwoEntry')
    )) {
      LSerror :: addErrorCode('LSsearch_13');
      return;
    }

    return true;
  }

  /**
   * Returns the id of table rows in the result sorted according to criteria
   * defined in the parameters
   *
   * @retval array The Table of id lines of results sorted
   **/
  public function getSortTable() {
    if (isset($this -> result['sort'][$this -> params['sortBy']][$this -> params['sortDirection']])) {
      return $this -> result['sort'][$this -> params['sortBy']][$this -> params['sortDirection']];
    }
    return range(0,($this -> total-1));
  }

  /**
   * List objects name
   *
   * @retval Array DN associate with name
   **/
  public function listObjectsName() {
    if (!LSsession::loadLSclass('LSsearchEntry')) {
      LSerror::addErrorCode('LSsession_05',$this -> LSobject);
      return;
    }

    $retval=array();

    if ($this -> total>0) {
      $sortTable=$this -> getSortTable();

      foreach ($sortTable as $key => $id) {
        $entry=new LSsearchEntry($this,$this -> LSobject,$this -> params,$this -> _hash,$this -> result['list'],$id);
        $retval[$entry->dn]=$entry->displayName;
      }
    }

    return $retval;
  }

  /**
   * List LSldapObjects
   *
   * @retval Array of LSldapObjects
   **/
  public function listObjects() {
    $retval=array();

    if ($this -> total>0) {
      $sortTable=$this -> getSortTable();

      $c=0;
      foreach ($sortTable as $key => $id) {
        $retval[$c]=new $this -> LSobject();
        $retval[$c] -> loadData($this -> result['list'][$id]['dn']);
        $c++;
      }
    }

    return $retval;
  }

  /**
   * List objects dn
   *
   * @retval Array of DN
   **/
  public function listObjectsDn() {
    $retval=array();

    if ($this -> total>0) {
      $sortTable=$this -> getSortTable();

      $c=0;
      foreach ($sortTable as $key => $id) {
        $retval[$c] = $this -> result['list'][$id]['dn'];
        $c++;
      }
    }

    return $retval;
  }

  /**
   * CLI search command
   *
   * @param[in] $command_args array Command arguments :
   *   - Positional arguments :
   *     - LSobject type
   *     - patterns
   *   - Optional arguments :
   *     - -f|--filter : LDAP filter string
   *     - -b|--basedn : LDAP base DN
   *     - --subDn : LDAP sub DN
   *     - -s|--scope : LDAP search scope (sub, one, base)
   *     - -l|--limit : search result size limit
   *     - -a|--approx : approximative search on provided pattern
   *     - -r|--recursive : recursive search
   *     - --sort-by : Sort by specific attribute/column
   *     - -R|--reverse : reverse search result
   *     - --sort-limit : Sort limit (in number of objects found)
   *     - --display-subdn : Display subDn in result
   *     - --display-format : Display format of objectName
   *     - -N|--nb-obj-by-page : number of object by page
   *     - -W|--without-cache : Disable cache
   *     - -e|--extra-columns : Display extra columns
   *     - -p|--page : page number to show (starting by 1, default: first one)
   *
   * @retval boolean True on succes, false otherwise
   **/
  public static function cli_search($command_args) {
    $logger = LSlog :: get_logger(get_called_class());
    $objType = null;
    $patterns = array();
    $params = array(
      'sortDirection' => 'ASC',
      'extraDisplayedColumns' => false,
    );
    $page_nb = 1;
    for ($i=0; $i < count($command_args); $i++) {
      switch ($command_args[$i]) {
        case '-f':
        case '--filter':
          $params['filter'] = $command_args[++$i];
          break;
        case '-b':
        case '--basedn':
          $params['basedn'] = $command_args[++$i];
          break;
        case '--subdn':
          $params['subdn'] = $command_args[++$i];
          break;
        case '-s':
        case '--scope':
          $params['scope'] = $command_args[++$i];
          break;
        case '-s':
        case '--scope':
          $params['scope'] = $command_args[++$i];
          break;
        case '-l':
        case '--limit':
          $params['sizelimit'] = intval($command_args[++$i]);
          break;
        case '-a':
        case '--approx':
          $params['approx'] = true;
          break;
        case '-r':
        case '--recursive':
          $params['recursive'] = true;
          break;
        case '--sort-by':
          $params['sortBy'] = $command_args[++$i];
          break;
        case '-R':
        case '--reverse':
          $params['sortDirection'] = 'DESC';
          break;
        case '--sort-limit':
          $params['sortlimit'] = intval($command_args[++$i]);
          break;
        case '--sort-limit':
          $params['sortlimit'] = intval($command_args[++$i]);
          break;
        case '--display-subdn':
          $params['displaySubDn'] = true;
          break;
        case '--display-format':
          $params['displayFormat'] = boolval($command_args[++$i]);
          break;
        case '-N':
        case '--nb-obj-by-page':
          $params['nbObjectsByPage'] = intval($command_args[++$i]);
          break;
        case '-W':
        case '--without-cache':
          $params['withoutCache'] = True;
          break;
        case '-e':
        case '--extra-columns':
          $params['extraDisplayedColumns'] = True;
          break;
        case '-p':
        case '--page':
          $page_nb = intval($command_args[++$i]);
          break;
        default:
          if (is_null($objType)) {
            $objType = $command_args[$i];
          }
          elseif (substr($command_args[$i], 0, 1) == '-') {
            LScli :: usage("Invalid parameter '".$command_args[$i]."'");
          }
          else {
            $patterns[] = $command_args[$i];
          }
      }
    }

    if (is_null($objType))
      LScli :: usage('You must provide LSobject type.');

    // Load Console Table lib
    $console_table_path = LSconfig :: get('ConsoleTable', 'Console/Table.php', 'string');
    if (!LSsession :: includeFile($console_table_path, true))
      $logger -> fatal('Fail to load ConsoleTable library.');

    if (!empty($patterns))
      $params['pattern'] = implode(' ', $patterns);

    $search = new LSsearch($objType, 'CLI', array(), true);

    // Set search params
    $logger -> debug('Search parameters : '.varDump($params));
    if (!$search -> setParams($params))
      $logger -> fatal('Fail to set search parameters.');

    // Run search
    if (!$search -> run())
      $logger -> fatal('Fail to run search.');

    // Retrieve page
    $page = $search -> getPage(($page_nb-1));
    /*
     * $page = array(
     *   'nb' => $page,
     *   'nbPages' => 1,
     *   'list' => array(),
     *   'total' => $this -> total
     * );
     */

    // Check page
    if (!is_array($page) || $page_nb > $page['nbPages'])
      $logger -> fatal("Fail to retreive page #$page_nb.");
    if (empty($page['list'])) {
      echo "No $objType object found.\n";
      exit(0);
    }

    // Create result table with its header
    $tbl = new Console_Table();
    $headers = array('DN', 'Name');
    if ($search -> displaySubDn)
      $headers[] = $search -> label_level;
    if ($search -> extraDisplayedColumns) {
      foreach ($search -> visibleExtraDisplayedColumns as $cid => $conf) {
        $headers[] = $conf['label'];
      }
    }
    $tbl->setHeaders($headers);

    // Add one line for each object found (in page)
    foreach($page['list'] as $obj) {
      $row = array(
        $obj -> dn,
        $obj -> displayName,
      );
      if ($search -> displaySubDn)
        $row[] = $obj -> subDn;
      if ($search -> extraDisplayedColumns) {
        foreach ($search -> visibleExtraDisplayedColumns as $cid => $conf) {
          $row[] = $obj -> $cid;
        }
      }
      $tbl->addRow($row);
    }
    echo $tbl->getTable();
    echo "Page ".($page['nb']+1)." on ".$page['nbPages']."\n";
    return true;
  }

  /**
   * Args autocompleter for CLI command search
   *
   * @param[in] $command_args array List of already typed words of the command
   * @param[in] $comp_word_num int The command word number to autocomplete
   * @param[in] $comp_word string The command word to autocomplete state
   * @param[in] $opts array List of global available options
   *
   * @retval array List of available options for the word to autocomplete
   **/
  public static function cli_search_args_autocompleter($command_args, $comp_word_num, $comp_word, $opts) {
    $command_opts = array (
      '-f', '--filter',
      '-b', '--basedn',
      '--subdn',
      '-s', '--scope',
      '-l', '--limit',
      '-a', '--approx',
      '-r', '--recursive',
      '--sort-by',
      '-R', '--reverse',
      '--sort-limit',
      '--display-subdn',
      '--display-format',
      '-N', '--nb-obj-by-page',
      '-W', '--without-cache',
      '-e', '--extra-columns',
      '-p', '--page',
    );

    // Detect positional args
    $objType = null;
    $objType_arg_num = null;
    $patterns = array();
    $extra_columns = false;
    for ($i=0; $i < count($command_args); $i++) {
      if (!in_array($command_args[$i], $command_opts) || in_array($command_args[$i], $opts)) {
        // If object type not defined
        if (is_null($objType)) {
          // Check object type exists
          $objTypes = LScli :: autocomplete_LSobject_types($command_args[$i]);

          // Load it if exist and not trying to complete it
          if (in_array($command_args[$i], $objTypes) && $i != $comp_word_num) {
            LSsession :: loadLSobject($command_args[$i], false);
          }

          // Defined it
          $objType = $command_args[$i];
          $objType_arg_num = $i;
        }
        else
          $patterns[] = $command_args[$i];
      }
      else {
        switch ($command_args[$i]) {
          case '-e':
          case '--extra-columns':
            $extra_columns = true;
            LSlog :: debug('Extra columns enabled');
            break;
        }
      }
    }

    // Handle completion of args value
    LSlog :: debug("Last complete word = '".$command_args[$comp_word_num-1]."'");
    switch ($command_args[$comp_word_num-1]) {
      case '--subdn':
        LScli :: need_ldap_con();
        $subDns = LSsession :: getSubDnLdapServer();
        if (is_array($subDns)) {
          $subDns = array_keys($subDns);
          LSlog :: debug('List of available subDns: '.implode(', ', $subDns));
        }
        else
          $subDns = array();
        return LScli :: autocomplete_opts($subDns, $comp_word);
      case '-s':
      case '--scope':
        return LScli :: autocomplete_opts(array('sub', 'one', 'base'), $comp_word);
      case '-f':
      case '--filter':
      case '-b':
      case '--basedn':
        // This args need string value that can't be autocomplete: stop autocompletion
        return array();
      case '-l':
      case '--limit':
      case '--sort-limit':
      case '-N':
      case '--nb-obj-by-page':
      case '-p':
      case '--page':
        return LScli :: autocomplete_int($comp_word);
      case '--sort-by':
        $bys = array('displayName', 'subDn');
        if ($objType && $extra_columns) {
          $extraDisplayedColumns = LSconfig::get("LSobjects.$objType.LSsearch.extraDisplayedColumns", array());
          if (is_array($extraDisplayedColumns))
            $bys = array_merge($bys, array_keys($extraDisplayedColumns));
        }
        LSlog :: debug('Available sort-bys clauses: '.implode(', ', $bys));
        return LScli :: autocomplete_opts($bys, $comp_word);
    }
    $opts = array_merge($opts, $command_opts);

    // If objType not already choiced (or currently autocomplete), add LSobject types to available options
    if (!$objType || $objType_arg_num == $comp_word_num)
      $opts = array_merge($opts, LScli :: autocomplete_LSobject_types($comp_word));

    return LScli :: autocomplete_opts($opts, $comp_word);
  }
}

/**
 * Error Codes
 **/
LSerror :: defineError('LSsearch_01',
_("LSsearch : Invalid filter : %{filter}.")
);
LSerror :: defineError('LSsearch_02',
_("LSsearch : Invalid basedn : %{basedn}.")
);
LSerror :: defineError('LSsearch_03',
_("LSsearch : Invalid value for %{param} parameter.")
);
LSerror :: defineError('LSsearch_04',
_("LSsearch : Invalid size limit. Must be an integer greater or equal to 0.")
);
LSerror :: defineError('LSsearch_05',
_("LSsearch : Invalid parameter %{attr}. Must be an boolean.")
);
LSerror :: defineError('LSsearch_06',
_("LSsearch : Invalid parameter attributes. Must be an string or an array of strings.")
);
LSerror :: defineError('LSsearch_07',
_("LSsearch : Can't build attributes list for make filter.")
);
LSerror :: defineError('LSsearch_08',
_("LSsearch : Error building filter with attribute '%{attr}' and pattern '%{pattern}'")
);
LSerror :: defineError('LSsearch_09',
_("LSsearch : Error combining filters.")
);
LSerror :: defineError('LSsearch_10',
_("LSsearch : Invalid pattern.")
);
LSerror :: defineError('LSsearch_11',
_("LSsearch : Invalid attribute %{attr} in parameters.")
);
LSerror :: defineError('LSsearch_12',
_("LSsearch : Error during the search.")
);
LSerror :: defineError('LSsearch_13',
_("LSsearch : Error sorting the search.")
);
LSerror :: defineError('LSsearch_14',
_("LSsearch : The function of the custum information %{name} is not callable.")
);
LSerror :: defineError('LSsearch_15',
_("LSsearch : Invalid predefinedFilter for LSobject type %{type} : %{label} (filter : %{filter}).")
);
LSerror :: defineError('LSsearch_16',
_("LSsearch : Error during execution of the custom action %{customAction}.")
);
LSerror :: defineError('LSsearch_17',
_("LSsearch : Invalid search pattern.")
);

// LScli
LScli :: add_command(
    'search',
    array('LSsearch', 'cli_search'),
    'Search LSobject',
    '[object type] [pattern1] [pattern2 ...]',
    array(
    '   - Positional arguments :',
    '     - LSobject type',
    '     - patterns',
    '',
    '   - Optional arguments :',
    '     - -f|--filter : LDAP filter string',
    '     - -b|--basedn : LDAP base DN',
    '     - --subDn : LDAP sub DN',
    '     - -s|--scope : LDAP search scope (sub, one, base)',
    '     - -l|--limit : search result size limit',
    '     - -a|--approx : approximative search on provided pattern',
    '     - -r|--recursive : recursive search',
    '     - --sort-by : Sort by specific attribute/column',
    '     - -R|--reverse : reverse search result',
    '     - --sort-limit : Sort limit (in number of objects found)',
    '     - --display-subdn : Display subDn in result',
    '     - --display-format : Display format of objectName',
    '     - -N|--nb-obj-by-page : number of object by page',
    '     - -W|--without-cache : Disable cache',
    '     - -e|--extra-columns : Display extra columns',
    '     - -p|--page : page number to show (starting by 1, default: first one)',
  ),
  true,
  array('LSsearch', 'cli_search_args_autocompleter'),
);
