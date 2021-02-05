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

/*
 * Common routing handlers
 */

/*
 * Handle index request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_index($request) {
  // Redirect to default view (if defined)
  LSsession :: redirectToDefaultView();

  // Define page title
  LStemplate :: assign('pagetitle', _('Home'));

  // Template
  LSsession :: setTemplate('homepage.tpl');

  // Display template
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^(index\.php|index)?$#', 'handle_index', true);

/*
 * Handle ajax keepLSsession request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_ajax_keepLSsession($request) {
  LSsession :: displayAjaxReturn(null);
}
LSurl :: add_handler('#^ajax/keepLSsession/?$#', 'handle_ajax_keepLSsession', true);

/*
 * Handle ajax request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_ajax($request) {
  $data = null;
  switch ($request -> type) {
    case 'class':
      $class = $request -> type_value;
      if (LSsession :: loadLSclass($class)) {
        $meth = 'ajax_'.$request -> action;
        if (method_exists($class, $meth)) {
           $class :: $meth($data);
        }
      }
      break;
    case 'addon':
      $addon = $request -> type_value;
      if (LSsession :: loadLSaddon($addon)) {
        $func = 'ajax_'.$request -> action;
        if (function_exists($func)) {
          $func = new ReflectionFunction($func);
          if (basename($func->getFileName()) == "LSaddons.$addon.php") {
            $func->invokeArgs(array(&$data));
          }
          else {
            LSerror :: addErrorCode('LSsession_21',array('func' => $func -> getName(),'addon' => $addon));
          }
        }
      }
      break;
    default:
      LSlog :: fatal('Unsupported AJAX request type !');
      exit();
  }
  LSsession :: displayAjaxReturn($data);
}
// TODO : find a proper solution for noLSsession URL parameter
LSurl :: add_handler('#^ajax/(?P<type>class|addon)/(?P<type_value>[^/]+)/(?P<action>[^/]+)/?$#', 'handle_ajax', (!isset($_REQUEST['noLSsession'])));

/*
 * Handle old index_ajax.php request for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_index_ajax_php($request) {
  LSerror :: addErrorCode('LSsession_26', 'index_ajax.php');
  LSlog :: warning(
    getFData(
      "Handling old URL style redirection:\n - Requested URL: '%{old}'\n - Redirection URL: '%{new}'\n - Referer: %{referer}",
      array (
        'old' => $request -> current_url,
        'new' => 'Redirection unsupported',
        'referer' => ($request -> referer?"'".$request -> referer."'":'Unknown (direct access or hidden by web-browser)'),
      )
    )
  );
  LSsession :: displayAjaxReturn(null);
}
LSurl :: add_handler('#^index_ajax\.php#', 'handle_old_index_ajax_php', false);

/*
 * Handle global seearch request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_global_search($request) {
  // Check global search is enabled
  if (!LSsession :: globalSearch()) {
    LSurl :: error_404($request);
    return false;
  }

  if (!LSsession :: loadLSclass('LSsearch')) {
    LSsession :: addErrorCode('LSsession_05','LSsearch');
    LSsession :: displayTemplate();
    return false;
  }

  $LSaccess = LSsession :: getLSaccess();
  $pattern = (isset($_REQUEST['pattern'])?$_REQUEST['pattern']:'');
  if (empty($pattern)) {
    LSerror :: addErrorCode(false, _('You must provide pattern for global search.'));
    LSurl :: redirect();
  }

  $LSview_actions=array();
  $LSview_actions['refresh'] = array (
    'label' => _('Refresh'),
    'url' => 'search?pattern='.urlencode($pattern).'&refresh=1',
    'action' => 'refresh'
  );
  LStemplate :: assign('LSview_actions', $LSview_actions);

  if (LSsession :: loadLSclass('LSform')) {
    LSform :: loadDependenciesDisplayView();
  }

  $onlyOne = true;
  $onlyOneObject = false;
  $pages=array();
  foreach ($LSaccess as $LSobject => $label) {
    if ( $LSobject == "SELF" || !LSsession :: loadLSobject($LSobject) )
      continue;
    if (!LSconfig::get("LSobjects.$LSobject.globalSearch", true, 'bool'))
      continue;

    $object = new $LSobject();
    LStemplate :: assign('pagetitle', $object -> getLabel());

    $LSsearch = new LSsearch($LSobject, 'LSview');
    $LSsearch -> setParamsFromRequest();

    $LSsearch -> run();

    if ($LSsearch -> total > 0) {
      $page = $LSsearch -> getPage(0);
      LStemplate :: assign('page', $page);
      LStemplate :: assign('LSsearch', $LSsearch);
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
      $LSsearch -> afterUsingResult();
    }
  }

  if ($onlyOne && $onlyOneObject && isset($_REQUEST['LSsearch_submit'])) {
    LSurl :: redirect('object/'.$onlyOneObject['LSobject'].'/'.urlencode($onlyOneObject['dn']));
  }

  LStemplate :: assign('pattern',$pattern);
  LStemplate :: assign('pages',$pages);
  LSsession :: setTemplate('global_search.tpl');

  // Display template
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^search/?$#', 'handle_global_search');


/*
 * Handle old global_search.php request for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_global_search_php($request) {
  if (!isset($_GET['pattern']))
    $url = null;
  else {
    $url = "search?pattern=".$_GET['pattern'];
    if (isset($_GET['LSsearch_submit']))
      $url .= "&LSsearch_submit";
    if (isset($_GET['refresh']))
      $url .= "&refresh";
  }
  LSerror :: addErrorCode('LSsession_26', 'global_search.php');
  LSlog :: warning(
    getFData(
      "Handling old URL style redirection:\n - Requested URL: '%{old}'\n - Redirection URL: '%{new}'\n - Referer: %{referer}",
      array (
        'old' => $request -> current_url,
        'new' => ($url?$url:'missing parameter to compose URL'),
        'referer' => ($request -> referer?"'".$request -> referer."'":'Unknown (direct access or hidden by web-browser)'),
      )
    )
  );
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^global_search\.php#', 'handle_old_global_search_php', false);

/*
 * Handle static file request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_static_file($request) {
  switch ($request -> type) {
    case 'image':
      $path = LStemplate :: getImagePath($request -> file);
      $mime_type = null;
      break;
    case 'css':
      $path = LStemplate :: getCSSPath($request -> file);
      $mime_type = 'text/css';
      break;
    case 'js':
      $path = LStemplate :: getJSPath($request -> file);
      $mime_type = 'text/javascript';
      break;
  }
  if ($path  && is_file($path)) {
   dumpFile($path, $mime_type);
  }
  LSurl :: error_404($request);
}
LSurl :: add_handler('#^(?P<type>image|css|js)/(?P<file>[^/]+)$#', 'handle_static_file', false);

/*
 * Handle default browser favicon.ico request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 */
function handle_favicon_ico_view($request) {
  LSurl :: redirect('image/favicon');
}
LSurl :: add_handler('#^favicon\.ico#', 'handle_favicon_ico_view', false);

/*
 * Handle libs file request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_libs_file($request) {
  $path = LStemplate :: getLibFilePath($request -> file);
  if ($path  && is_file($path)) {
    switch (strtolower(substr($path, -4))) {
      case '.css':
        $mime_type = 'text/css';
        break;
      case '.js':
        $mime_type = 'text/javascript';
        break;
      default:
        $mime_type = null;
    }
    dumpFile($path, $mime_type);
  }
  LSurl :: error_404($request);
}
LSurl :: add_handler('#^libs/(?P<file>.+)$#', 'handle_libs_file', false);

/*
 * Handle tmp file request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_tmp_file($request) {
  $path = LSsession :: getTmpFileByFilename($request -> filename);
  if ($path && is_file($path)) {
   dumpFile($path);
  }
  LSurl :: error_404($request);
}
LSurl :: add_handler('#^tmp/(?P<filename>[^/]+)$#', 'handle_tmp_file');

/*
 ************************************************************
 * LSobject views
 ************************************************************
 */

/*
 * LSobject view helper to retreive LSobject from request
 *
 * This helper load LSobject type from 'LSobject' request
 * parameter, check user access. If instanciate parameter
 * is True, an object of this type will be instanciate and
 * return. Moreover, if 'dn' request parameter is present,
 * the data of this object will be loaded from LDAP.
 *
 * @param[in] $request LSurlRequest The request
 * @param[in] $instanciate boolean Instanciate and return an object (optional, default: true)
 * @param[in] $check_access callable|null Permit to specify check access method (optional, default: LSsession :: canAccess())
 * @param[in] $api_mode boolean Enable API mode (optional, default: false)
 *
 * @retval LSobject|boolean The instanciated LSobject (or True if $instanciate=false), or False
 *                          on error/access refused
 */
function get_LSobject_from_request($request, $instanciate=true, $check_access=null, $api_mode=false) {
    $LSobject = $request -> LSobject;
    $dn = (isset($request -> dn)?$request -> dn:null);

    // Handle $check_access parameter
    if (is_null($check_access))
      $check_access = array('LSsession', 'canAccess');

    // Handle SELF redirect
    if ( !$api_mode && $LSobject == 'SELF' ) {
      $LSobject = LSsession :: getLSuserObject() -> getType();
      $dn = LSsession :: getLSuserObjectDn();
      LSurl :: redirect("object/$LSobject/".urlencode($dn));
    }

    // If $dn, check user access to this LSobject
    if ($dn) {
      if (!call_user_func($check_access, $LSobject, $dn)) {
        LSerror :: addErrorCode('LSsession_11');
        if ($api_mode)
          LSsession :: displayAjaxReturn();
        else
          LSsession :: displayTemplate();
        return false;
      }
    }
    else if (!LSsession :: in_menu($LSobject) && !call_user_func($check_access, $LSobject)) {
      LSerror :: addErrorCode('LSsession_11');
      if ($api_mode)
        LSsession :: displayAjaxReturn();
      else
        LSsession :: displayTemplate();
      return false;
    }

    // Load LSobject type
    if ( !LSsession :: loadLSobject($LSobject) )
      return false;

    // If not $instanciate (and $dn not defined), just return true
    if (!$instanciate && !$dn)
      return True;

    // Instanciate object
    $object = new $LSobject();

    // Load $dn data (if defined)
    if ($dn && !$object -> loadData($dn)) {
      LSurl :: error_404($request);
      return false;
    }

    return $object;
}

/*
 * Handle LSobject search/list request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_LSobject_search($request) {
  $object = get_LSobject_from_request($request, true);
  if (!$object)
   return;

  $LSobject = $object -> getType();

  if (!LSsession :: loadLSclass('LSsearch')) {
    LSsession :: addErrorCode('LSsession_05', 'LSsearch');
    LSsession :: displayTemplate();
    return false;
  }

  // Set pagetitle
  LStemplate :: assign('pagetitle', $object -> getLabel());

  // Instanciate a LSsearch
  $LSsearch = new LSsearch($LSobject, 'LSview', null, (isset($_REQUEST['reset'])));
  $LSsearch -> setParam('extraDisplayedColumns', True);
  $LSsearch -> setParamsFromRequest();

  // List user available actions for this LSobject type
  $LSview_actions = array();
  if(LSsession :: canCreate($LSobject)) {
    $LSview_actions['create'] = array (
      'label' => _('New'),
      'url' => "object/$LSobject/create",
      'action' => 'create'
    );
    if ($object -> listValidIOformats()) {
     $LSview_actions['import'] = array (
      'label' => _('Import'),
      'url' => "object/$LSobject/import",
      'action' => 'import'
     );
     $LSview_actions['export'] = array (
      'label' => _('Export'),
      'url' => "object/$LSobject/export",
      'action' => 'export'
     );
    }
  }
  $LSview_actions['refresh'] = array (
    'label' => _('Refresh'),
    'url' => "object/$LSobject?refresh",
    'action' => 'refresh'
  );
  $LSview_actions['reset'] = array (
    'label' => _('Reset'),
    'url' => "object/$LSobject?reset",
    'action' => 'reset'
  );

  // Custum Actions
  $customActionsConfig = LSconfig :: get('LSobjects.'.$LSobject.'.LSsearch.customActions');
  if (is_array($customActionsConfig)) {
    foreach($customActionsConfig as $name => $config) {
      if (LSsession :: canExecuteLSsearchCustomAction($LSsearch,$name)) {
        $LSview_actions[] = array (
          'label' => ((isset($config['label']))?__($config['label']):__($name)),
          'hideLabel' => ((isset($config['hideLabel']) && $config['hideLabel'])?$config['hideLabel']:False),
          'helpInfo' => ((isset($config['helpInfo']))?__($config['helpInfo']):False),
          'url' => "object/$LSobject/customAction/$name",
          'action' => ((isset($config['icon']))?$config['icon']:'generate'),
          'class' => 'LScustomActions'.((isset($config['noConfirmation']) && $config['noConfirmation'])?' LScustomActions_noConfirmation':'')
        );
      }
    }
  }

  // Run search
  $LSsearch -> run();
  $LSsearch -> redirectWhenOnlyOneResult();

  // Handle page parameter and retreive corresponding page from search
  $page = (isset($_REQUEST['page'])?(int)$_REQUEST['page']:0);
  $page = $LSsearch -> getPage($page);

  // Set template variables
  LStemplate :: assign('page', $page);
  LStemplate :: assign('LSsearch', $LSsearch);
  LStemplate :: assign('LSview_actions', $LSview_actions);
  LStemplate :: assign('searchForm', array (
    'action' => "object/$LSobject",
    'recursive' => (! LSsession :: isSubDnLSobject($LSobject) && LSsession :: subDnIsEnabled() ),
    'labels' => array (
      'submit' => _('Search'),
      'approx' => _('Approximative search'),
      'recursive' => _('Recursive search')
    ),
    'values' => array (
      'pattern' => $LSsearch -> getParam('pattern'),
      'approx' => $LSsearch -> getParam('approx'),
      'recursive' => $LSsearch -> getParam('recursive')
    ),
    'names' => array (
      'submit' => 'LSsearch_submit'
    ),
    'hiddenFields' => $LSsearch -> getHiddenFieldForm(),
    'predefinedFilter' => $LSsearch -> getParam('predefinedFilter')
  ));


  if (LSsession :: loadLSclass('LSform')) {
    LSform :: loadDependenciesDisplayView($object, true);
  }

  // Set & display template
  LSsession :: setTemplate('viewSearch.tpl');
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^object/(?P<LSobject>[^/]+)/?$#', 'handle_LSobject_search');

/*
 * Handle LSobject search custom action request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_LSobject_search_customAction($request) {
  $object = get_LSobject_from_request($request, true);
  if (!$object)
   return;

  if (!LSsession :: loadLSclass('LSsearch')) {
    LSsession :: addErrorCode('LSsession_05', 'LSsearch');
    LSsession :: displayTemplate();
    return false;
  }

  $LSobject = $object -> getType();
  $customAction = $request -> customAction;

  // Instanciate a LSsearch
  $LSsearch = new LSsearch($LSobject, 'LSview');
  $LSsearch -> setParam('extraDisplayedColumns', True);
  $LSsearch -> setParamsFromRequest();

  // Check user right on this search customAction
  if ( !LSsession :: canExecuteLSsearchCustomAction($LSsearch, $customAction) ) {
    LSerror :: addErrorCode('LSsession_11');
    LSsession :: displayTemplate();
    return false;
  }

  $config = LSconfig :: get("LSobjects.$LSobject.LSsearch.customActions.$customAction");
  $title = isset($config['label'])?__($config['label']):$customAction;

  // Check search customAction function
  if (!isset($config['function']) || !is_callable($config['function'])) {
    LSerror :: addErrorCode(
      'LSsession_13',
      array(
        'customAction' => $title,
        'function' => (isset($config['function'])?getCallableName($config['function']):_('undefined'))
      )
    );
    LSsession :: displayTemplate();
    return false;
  }

  $objectname = $object -> getDisplayName();

  // Define page title
  LStemplate :: assign('pagetitle', $title);

  // Run search customAction (if validated or no confirmation need)
  if (isset($_GET['valid']) || $config['noConfirmation']) {
    if (call_user_func_array($config['function'], array(&$LSsearch))) {
      if (isset($config['disableOnSuccessMsg']) && $config['disableOnSuccessMsg'] != true) {
        LSsession :: addInfo(
          (isset($config['onSuccessMsgFormat']) && $config['onSuccessMsgFormat'])?
          getFData(__($config['onSuccessMsgFormat']), $objectname):
          getFData(_('The custom action %{title} have been successfully execute on this search.'), $title)
        );
      }
      if (!isset($config['redirectToObjectList']) || $config['redirectToObjectList']) {
        LSurl :: redirect("object/$LSobject?refresh");
      }
    }
    else {
      LSerror :: addErrorCode('LSsearch_16', $customAction);
    }
    // Custom action executed: show its template (if not already redirect)
    LSsession :: displayTemplate();
    return;
  }

  // Need confirmation: set template variables and show it
  LStemplate :: assign(
    'question',
    (
      isset($config['question_format'])?
      getFData(__($config['question_format']), $title):
      getFData(_('Do you really want to execute custom action %{title} on this search ?'), $title)
    )
  );
  LStemplate :: assign('validation_url', "object/$LSobject/customAction/".urlencode($customAction)."?valid");
  LStemplate :: assign('validation_label', _('Validate'));

  // Set & display template
  LSsession :: setTemplate('question.tpl');
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^object/(?P<LSobject>[^/]+)/customAction/(?P<customAction>[^/]+)/?$#', 'handle_LSobject_search_customAction');

/*
 * Handle old custom_search_action.php request for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_custom_search_action_php($request) {
  if (!isset($_GET['LSobject']) || !isset($_GET['customAction']))
    $url = null;
  elseif (isset($_GET['valid']))
    $url = "object/".$_GET['LSobject']."/customAction/".$_GET['customAction']."?valid";
  else
    $url = "object/".$_GET['LSobject']."/customAction/".$_GET['customAction'];
  LSerror :: addErrorCode('LSsession_26', 'custom_search_action.php');
  LSlog :: warning(
    getFData(
      "Handling old URL style redirection:\n - Requested URL: '%{old}'\n - Redirection URL: '%{new}'\n - Referer: %{referer}",
      array (
        'old' => $request -> current_url,
        'new' => ($url?$url:'missing parameter to compose URL'),
        'referer' => ($request -> referer?"'".$request -> referer."'":'Unknown (direct access or hidden by web-browser)'),
      )
    )
  );
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^custom_search_action\.php#', 'handle_old_custom_search_action_php', false);

/*
 * Handle LSobject select request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_LSobject_select($request) {
  if (!LSsession :: loadLSclass('LSselect')) {
    LSsession :: addErrorCode('LSsession_05', 'LSsearch');
    LSsession :: displayTemplate();
    return false;
  }

  if (!LSselect :: exists($request->LSselect_id)) {
    LSurl :: error_404($request);
    return;
  }

  // Instanciate LSsearch
  $LSsearch = LSselect :: getSearch(
    $request->LSselect_id,
    (isset($_REQUEST['LSobject'])?$_REQUEST['LSobject']:null)
  );
  if (!$LSsearch)
    LSlog :: fatal('Fail to retreive search from context.');

  $LSobject = $LSsearch -> LSobject;
  $object = new $LSobject();

  // Handle form POST data
  $LSsearch -> setParamsFromRequest();
  $LSsearch -> setParam('nbObjectsByPage', NB_LSOBJECT_LIST_SELECT);

  $page = (isset($_REQUEST['page'])?(int)$_REQUEST['page']:0);

  // Run search
  $LSsearch -> run();

  // Set template variables
  LStemplate :: assign('pagetitle', $object -> getLabel());
  LStemplate :: assign('LSview_actions',
    array(
      array (
        'label' => 'Refresh',
        'url' => "object/select/".$request->LSselect_id."?refresh",
        'action' => 'refresh'
      )
    )
  );
  LStemplate :: assign('searchForm',
    array (
      'action' => "object/select/".$request->LSselect_id,
      'recursive' => (! LSsession :: isSubDnLSobject($LSobject) && LSsession :: subDnIsEnabled() ),
      'multiple' => LSselect :: isMultiple($request->LSselect_id),
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
          'LSselect_id' => $request->LSselect_id,
          'multiple' => LSselect :: isMultiple($request->LSselect_id),
        )
      )
    )
  );
  LStemplate :: assign('page', $LSsearch -> getPage($page));
  LStemplate :: assign('LSsearch', $LSsearch);
  LStemplate :: assign('LSselect_id', $request->LSselect_id);
  LStemplate :: assign('selectable_object_types', LSselect :: getSelectableObjectTypes($request->LSselect_id));
  LStemplate :: assign('selectable_object_type', $LSobject);
  LStemplate :: assign('LSobject_list_objectname', $object -> getLabel());

  // Set & display template
  LSsession :: setTemplate(isset($_REQUEST['ajax'])?'select_table.tpl':'select.tpl');
  LSsession :: setAjaxDisplay();
  LSsession :: displayTemplate();
  $LSsearch->afterUsingResult();
}
LSurl :: add_handler('#^object/select/(?P<LSselect_id>[^/]+)/?$#', 'handle_LSobject_select');

/*
 * Handle old select.php request for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_select_php($request) {
  if (!isset($_GET['LSobject']))
    $url = null;
  else {
    $url = "object/".$_GET['LSobject'];
  }
  LSerror :: addErrorCode('LSsession_26', 'select.php');
  LSlog :: warning(
    getFData(
      "Handling old URL style redirection:\n - Requested URL: '%{old}'\n - Redirection URL: '%{new}'\n - Referer: %{referer}",
      array (
        'old' => $request -> current_url,
        'new' => ($url?$url:'LSobject parameter is missing to compose URL'),
        'referer' => ($request -> referer?"'".$request -> referer."'":'Unknown (direct access or hidden by web-browser)'),
      )
    )
  );
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^select\.php#', 'handle_old_select_php', false);

/*
 * Handle LSobject import request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_LSobject_import($request) {
  $object = get_LSobject_from_request($request, true);
  if (!$object)
   return;

  $ioFormats = array();
  $result = array();
  if ( LSsession :: loadLSclass('LSimport', null, true)) {  // import class with warning
    $ioFormats = $object->listValidIOformats();
    if (!is_array($ioFormats) || empty($ioFormats)) {
      $ioFormats = array();
      LSerror :: addErrorCode('LSsession_16');
    }
    else if (LSimport::isSubmit()) {
      $result = LSimport::importFromPostData();
      LSlog :: debug("LSimport::importFromPostData(): result = ".varDump($result));
    }
  }

  // Define page title & template variables
  LStemplate :: assign('pagetitle', _('Import').' : '.$object->getLabel());
  LStemplate :: assign('LSobject', $object -> getType());
  LStemplate :: assign('ioFormats', $ioFormats);
  LStemplate :: assign('result', $result);

  // Set & display template
  LSsession :: setTemplate('import.tpl');
  LStemplate :: addCssFile('LSform.css');
  LStemplate :: addCssFile('LSimport.css');
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^object/(?P<LSobject>[^/]+)/import/?$#', 'handle_LSobject_import');

/*
 * Handle old import.php request for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_import_php($request) {
  if (!isset($_GET['LSobject']))
    $url = null;
  else
    $url = "object/".$_GET['LSobject']."/import";
  LSerror :: addErrorCode('LSsession_26', 'import.php');
  LSlog :: warning(
    getFData(
      "Handling old URL style redirection:\n - Requested URL: '%{old}'\n - Redirection URL: '%{new}'\n - Referer: %{referer}",
      array (
        'old' => $request -> current_url,
        'new' => ($url?$url:'missing parameter to compose URL'),
        'referer' => ($request -> referer?"'".$request -> referer."'":'Unknown (direct access or hidden by web-browser)'),
      )
    )
  );
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^import\.php#', 'handle_old_import_php', false);


/*
 * Handle LSobject export request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_LSobject_export($request) {
  $object = get_LSobject_from_request($request, true);
  if (!$object)
   return;

  $ioFormats = array();
  $result = null;
  if ( LSsession :: loadLSclass('LSexport', null, true)) {  // Load class with warning
    $ioFormats = $object->listValidIOformats();
    if (!is_array($ioFormats) || empty($ioFormats)) {
      $ioFormats = array();
      LSerror :: addErrorCode('LSsession_16');
    }
    else if (isset($_REQUEST['ioFormat'])) {
      if (!LSexport::export($object, $_REQUEST['ioFormat']))
        LSlog :: error("An error occurred exporting ".$object -> type);
    }
  }

  // Define page title & template variables
  LStemplate :: assign('pagetitle', _('Export').' : '.$object->getLabel());
  LStemplate :: assign('LSobject', $object -> getType());
  LStemplate :: assign('ioFormats', $ioFormats);

  // Set & display template
  LSsession :: setTemplate('export.tpl');
  LStemplate :: addCssFile('LSform.css');
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^object/(?P<LSobject>[^/]+)/export/?$#', 'handle_LSobject_export');

/*
 * Handle LSobject create request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_LSobject_create($request) {
  $object = get_LSobject_from_request(
    $request,
    true,                             // instanciate object
    array('LSsession', 'canCreate')   // Check access method
  );
  if (!$object)
   return;

  $LSobject = $object -> getType();

  if (isset($_GET['load']) && $_GET['load']!='') {
    $form = $object -> getForm('create', urldecode($_GET['load']));
  }
  else {
    if (isset($_GET['LSrelation']) && isset($_GET['relatedLSobject']) && isset($_GET['relatedLSobjectDN'])) {
      if (LSsession :: loadLSobject($_GET['relatedLSobject']) && LSsession :: loadLSclass('LSrelation')) {
        $obj = new $_GET['relatedLSobject']();
        if ($obj -> loadData(urldecode($_GET['relatedLSobjectDN']))) {
          if (LSrelation :: exists($_GET['relatedLSobject'], $_GET['LSrelation'])) {
            $relation = new LSrelation($obj, $_GET['LSrelation']);
            if ($relation -> correctly_configured) {
              $attr = $relation -> relatedEditableAttribute;
              if (isset($object -> attrs[$attr])) {
                $value = $relation -> getRelatedKeyValue();
                if (is_array($value)) $value=$value[0];
                $object -> attrs[$attr] -> data = array($value);
              }
              else {
                LSerror :: addErrorCode('LSrelation_06',array('relation' => $relation -> getName(),'LSobject' => $obj -> getType()));
              }
            }
            else {
              LSlog :: warning("Relation '".$_GET['LSrelation']."' of object type '".$_GET['relatedLSobject']."' is not correctly configured.");
            }
          }
          else {
            LSlog :: warning("Relation '".$_GET['LSrelation']."' of object type '".$_GET['relatedLSobject']."' does not exists.");
          }
        }
        else {
          LSerror :: addErrorCode('LSsession_24');
        }
      }
    }
    $form = $object -> getForm('create');
  }

  if (isset($_REQUEST['LSform_dataEntryForm'])) {
    $form -> applyDataEntryForm((string)$_REQUEST['LSform_dataEntryForm']);
    LStemplate :: assign('LSform_dataEntryForm', (string)$_REQUEST['LSform_dataEntryForm']);
  }

  LStemplate :: assign('listAvailableDataEntryForm', LSform :: listAvailableDataEntryForm($LSobject));
  LStemplate :: assign('DataEntryFormLabel', _('Data entry form'));

  if ($form->validate()) {
    // Data update for LDAP object
    if ($object -> updateData('create')) {
      if (!LSerror::errorsDefined()) {
        LSsession :: addInfo(_("Object has been added."));
      }
      if (isset($_REQUEST['ajax'])) {
        LSsession :: displayAjaxReturn (
          array(
            'LSredirect' => "object/$LSobject/".urlencode($object -> getDn())
          )
        );
        exit();
      }
      else {
        if (!LSdebugDefined())
          LSurl :: redirect("object/$LSobject/".urlencode($object -> getDn()));
      }
    }
    else {
      if (isset($_REQUEST['ajax'])) {
        LSsession :: displayAjaxReturn (
          array(
            'LSformErrors' => $form -> getErrors()
          )
        );
        exit();
      }
    }
  }
  else if (isset($_REQUEST['ajax']) && $form -> definedError()) {
    LSsession :: displayAjaxReturn (
      array(
        'LSformErrors' => $form -> getErrors()
      )
    );
    exit();
  }
  // Define page title
  LStemplate :: assign('pagetitle',_('New').' : '.$object -> getLabel());
  $form -> display("object/$LSobject/create");

  // Set & display template
  LSsession :: setTemplate('create.tpl');
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^object/(?P<LSobject>[^/]+)/create/?$#', 'handle_LSobject_create');

/*
 * Handle old create.php request for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_create_php($request) {
  if (!isset($_GET['LSobject']))
    $url = null;
  else
    $url = "object/".$_GET['LSobject']."/create";
  LSerror :: addErrorCode('LSsession_26', 'create.php');
  LSlog :: warning(
    getFData(
      "Handling old URL style redirection:\n - Requested URL: '%{old}'\n - Redirection URL: '%{new}'\n - Referer: %{referer}",
      array (
        'old' => $request -> current_url,
        'new' => ($url?$url:'missing parameter to compose URL'),
        'referer' => ($request -> referer?"'".$request -> referer."'":'Unknown (direct access or hidden by web-browser)'),
      )
    )
  );
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^create\.php#', 'handle_old_create_php', false);

/*
 * Handle LSobject show request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_LSobject_show($request) {
  $object = get_LSobject_from_request($request, true);
  if (!$object)
   return;

  $LSobject = $object -> getType();
  $dn = $object -> getDn();

  // List user available actions for this LSobject
  $LSview_actions = array();
  if ( LSsession :: canEdit($LSobject, $dn) ) {
    $LSview_actions[] = array(
      'label' => _('Modify'),
      'url' => "object/$LSobject/".urlencode($dn)."/modify",
      'action' => 'modify'
    );
  }

  if (LSsession :: canCreate($LSobject)) {
    $LSview_actions[] = array(
      'label' => _('Copy'),
      'url' => "object/$LSobject/create?load=".urlencode($dn),
      'action' => 'copy'
    );
  }

  if (LSsession :: canRemove($LSobject, $dn)) {
    $LSview_actions[] = array(
      'label' => _('Delete'),
      'url' => "object/$LSobject/".urlencode($dn)."/remove",
      'action' => 'delete'
    );
  }

  // Custum Actions
  $customActionsConfig = LSconfig :: get('LSobjects.'.$LSobject.'.customActions');
  if (is_array($customActionsConfig)) {
    foreach($customActionsConfig as $name => $config) {
      if (LSsession :: canExecuteCustomAction($dn, $LSobject, $name)) {
        $LSview_actions[] = array (
          'label' => ((isset($config['label']))?__($config['label']):__($name)),
          'hideLabel' => ((isset($config['hideLabel']) && $config['hideLabel'])?$config['hideLabel']:False),
          'helpInfo' => ((isset($config['helpInfo']))?__($config['helpInfo']):False),
          'url' => "object/$LSobject/".urlencode($dn)."/customAction/".urlencode($name),
          'action' => ((isset($config['icon']))?$config['icon']:'generate'),
          'class' => 'LScustomActions'.((isset($config['noConfirmation']) && $config['noConfirmation'])?' LScustomActions_noConfirmation':'')
        );
      }
    }
  }

  $view = $object -> getView();
  $view -> displayView();

  // LSrelations
  if (LSsession :: loadLSclass('LSrelation')) {
    LSrelation :: displayInLSview($object);
  }

  LStemplate :: assign('pagetitle', (LSsession :: getLSuserObjectDn() == $dn?_('My account'):$object -> getDisplayName()));
  LStemplate :: assign('LSldapObject', $object);
  LStemplate :: assign('LSview_actions', $LSview_actions);


  // Set & display template
  LSsession :: setTemplate('view.tpl');
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^object/(?P<LSobject>[^/]+)/?(?P<dn>[^/]+)/?$#', 'handle_LSobject_show');

/*
 * Handle old view.php request for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_view_php($request) {
  if (!isset($_GET['LSobject']))
    $url = null;
  elseif (isset($_GET['dn']))
    $url = "object/".$_GET['LSobject']."/".$_GET['dn'];
  else
    $url = "object/".$_GET['LSobject'];
  LSerror :: addErrorCode('LSsession_26', 'view.php');
  LSlog :: warning(
    getFData(
      "Handling old URL style redirection:\n - Requested URL: '%{old}'\n - Redirection URL: '%{new}'\n - Referer: %{referer}",
      array (
        'old' => $request -> current_url,
        'new' => ($url?$url:'missing parameter to compose URL'),
        'referer' => ($request -> referer?"'".$request -> referer."'":'Unknown (direct access or hidden by web-browser)'),
      )
    )
  );
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^view\.php#', 'handle_old_view_php', false);

/*
 * Handle LSobject modify request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_LSobject_modify($request) {
  $object = get_LSobject_from_request(
    $request,
    true,                             // instanciate object
    array('LSsession', 'canEdit')     // Check access method
  );
  if (!$object)
   return;

  $LSobject = $object -> getType();
  $form = $object -> getForm('modify');
  if ($form->validate()) {
    // Update LDAP object data
    if ($object -> updateData('modify')) {
      // Update successful
      if (LSerror::errorsDefined()) {
        LSsession :: addInfo(_("The object has been partially modified."));
      }
      else {
        LSsession :: addInfo(_("The object has been modified successfully."));
      }
      if (isset($_REQUEST['ajax'])) {
        LSsession :: displayAjaxReturn (
          array(
            'LSredirect' => "object/$LSobject/".urlencode($object -> getDn())
          )
        );
        return true;
      }
      else {
        if (!LSdebugDefined()) {
          LSurl :: redirect("object/$LSobject/".urlencode($object -> getDn()));
        }
      }
    }
    else {
      if (isset($_REQUEST['ajax'])) {
        LSsession :: displayAjaxReturn (
          array(
            'LSformErrors' => $form -> getErrors()
          )
        );
        return true;
      }
    }
  }
  else if (isset($_REQUEST['ajax']) && $form -> definedError()) {
    LSsession :: displayAjaxReturn (
      array(
        'LSformErrors' => $form -> getErrors()
      )
    );
    return true;
  }

  // List user available actions for this LSobject
  $LSview_actions = array(
    array(
      'label' => _('View'),
      'url' => "object/$LSobject/".urlencode($object -> getDn()),
      'action' => 'view'
    ),
  );

  if (LSsession :: canRemove($LSobject,$object -> getDn())) {
    $LSview_actions[] = array(
      'label' => _('Delete'),
      'url' => "object/$LSobject/".urlencode($object -> getDn())."/remove",
      'action' => 'delete'
    );
  }
  LStemplate :: assign('LSview_actions',$LSview_actions);

  // Define page title
  LStemplate :: assign('pagetitle',_('Modify').' : '.$object -> getDisplayName());
  LStemplate :: assign('object_name', $object -> getDisplayName());
  $form -> display("object/$LSobject/".urlencode($object -> getDn())."/modify");

  // Set & display template
  LSsession :: setTemplate('modify.tpl');
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^object/(?P<LSobject>[^/]+)/(?P<dn>[^/]+)/modify/?$#', 'handle_LSobject_modify');

/*
 * Handle old modify.php request for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_modify_php($request) {
  if (!isset($_GET['LSobject']) || !isset($_GET['dn']))
    $url = null;
  else
    $url = "object/".$_GET['LSobject']."/".$_GET['dn']."/modify";
  LSerror :: addErrorCode('LSsession_26', 'modify.php');
  LSlog :: warning(
    getFData(
      "Handling old URL style redirection:\n - Requested URL: '%{old}'\n - Redirection URL: '%{new}'\n - Referer: %{referer}",
      array (
        'old' => $request -> current_url,
        'new' => ($url?$url:'missing parameter to compose URL'),
        'referer' => ($request -> referer?"'".$request -> referer."'":'Unknown (direct access or hidden by web-browser)'),
      )
    )
  );
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^modify\.php#', 'handle_old_modify_php', false);

/*
 * Handle LSobject remove request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_LSobject_remove($request) {
  $object = get_LSobject_from_request(
    $request,
    true,                             // instanciate object
    array('LSsession', 'canRemove')   // Check access method
  );
  if (!$object)
   return;

  $LSobject = $object -> getType();
  $dn = $object -> getDn();
  $objectname = $object -> getDisplayName();

  // Remove object (if validated)
  if (isset($_GET['valid'])) {
    if ($object -> remove()) {
      LSsession :: addInfo(getFData(_('%{objectname} has been successfully deleted.'), $objectname));
      LSurl :: redirect("object/$LSobject?refresh");
    }
    else {
      LSerror :: addErrorCode('LSldapObject_15', $objectname);
    }
  }

  // Define page title
  LStemplate :: assign('pagetitle', getFData(_('Deleting : %{objectname}'), $objectname));
  LStemplate :: assign('question', getFData(_('Do you really want to delete <strong>%{displayName}</strong> ?'), $objectname));
  LStemplate :: assign('validation_url', "object/$LSobject/".urlencode($dn)."/remove?valid");
  LStemplate :: assign('validation_label', _('Validate'));

  // List user available actions for this LSobject
  $LSview_actions = array(
    array(
      'label' => _('View'),
      'url' => "object/$LSobject/".urlencode($dn),
      'action' => 'view'
    ),
  );

  if (LSsession :: canEdit($LSobject, $dn)) {
    $LSview_actions[] = array(
      'label' => _('Modify'),
      'url' => "object/$LSobject/".urlencode($dn)."/modify",
      'action' => 'modify'
    );
  }
  LStemplate :: assign('LSview_actions',$LSview_actions);

  // Set & display template
  LSsession :: setTemplate('question.tpl');
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^object/(?P<LSobject>[^/]+)/(?P<dn>[^/]+)/remove/?$#', 'handle_LSobject_remove');

/*
 * Handle old remove.php request for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_remove_php($request) {
  if (!isset($_GET['LSobject']) || !isset($_GET['dn']))
    $url = null;
  elseif (isset($_GET['valid']))
    $url = "object/".$_GET['LSobject']."/".$_GET['dn']."/remove?valid";
  else
    $url = "object/".$_GET['LSobject']."/".$_GET['dn']."/remove";
  LSerror :: addErrorCode('LSsession_26', 'remove.php');
  LSlog :: warning(
    getFData(
      "Handling old URL style redirection:\n - Requested URL: '%{old}'\n - Redirection URL: '%{new}'\n - Referer: %{referer}",
      array (
        'old' => $request -> current_url,
        'new' => ($url?$url:'missing parameter to compose URL'),
        'referer' => ($request -> referer?"'".$request -> referer."'":'Unknown (direct access or hidden by web-browser)'),
      )
    )
  );
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^remove\.php#', 'handle_old_remove_php', false);

/*
 * Handle LSobject customAction request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_LSobject_customAction($request) {
  $object = get_LSobject_from_request($request);
  if (!$object)
   return;

  $LSobject = $object -> getType();
  $dn = $object -> getDn();
  $customAction = $request -> customAction;

  if ( !LSsession :: canExecuteCustomAction($dn, $LSobject, $customAction) ) {
    LSerror :: addErrorCode('LSsession_11');
    LSsession :: displayTemplate();
    return;
  }

  $config = LSconfig :: get("LSobjects.$LSobject.customActions.$customAction");
  $title = isset($config['label'])?__($config['label']):$customAction;

  // Check customAction function
  if (!isset($config['function']) || !is_callable($config['function'])) {
    LSerror :: addErrorCode(
      'LSsession_13',
      array(
        'customAction' => $title,
        'function' => (isset($config['function'])?getCallableName($config['function']):_('undefined'))
      )
    );
    LSsession :: displayTemplate();
    return;
  }

  $objectname = $object -> getDisplayName();
  // Define page title
  LStemplate :: assign('pagetitle', $title.' : '.$objectname);

  // Run customAction (if validated or noConfirmation required)
  if (isset($_GET['valid']) || (isset($config['noConfirmation']) && $config['noConfirmation'])) {
    LStemplate :: assign('pagetitle', $title.' : '.$objectname);
    if (call_user_func_array($config['function'], array(&$object))) {
      if ($config['disableOnSuccessMsg'] != true) {
        if ($config['onSuccessMsgFormat']) {
          LSsession :: addInfo(getFData(__($config['onSuccessMsgFormat']), $objectname));
        }
        else {
          LSsession :: addInfo(
            getFData(
              _('The custom action %{customAction} have been successfully execute on %{objectname}.'),
              array('objectname' => $objectname, 'customAction' => $customAction)
            )
          );
        }
      }
      if (isset($config['redirectToObjectList']) && $config['redirectToObjectList']) {
        LSurl :: redirect("object/$LSobject?refresh");
      }
      else if (!isset($config['noRedirect']) || !$config['noRedirect']) {
        LSurl :: redirect("object/$LSobject/".urlencode($dn));
      }
    }
    else {
      LSerror :: addErrorCode('LSldapObject_31', array('objectname' => $objectname, 'customAction' => $customAction));
    }
    // Custom action executed: show its template (if not already redirect)
    LSsession :: displayTemplate();
    return;
  }

  // Need confirmation: set template variables and show it
  LStemplate :: assign(
    'question',
    (
      isset($config['question_format'])?
      getFData(__($config['question_format']), $objectname):
      getFData(
        _('Do you really want to execute custom action %{customAction} on %{objectname} ?'),
        array('objectname' => $objectname, 'customAction' => $customAction)
      )
    )
  );
  LStemplate :: assign('validation_url', "object/$LSobject/".urlencode($dn)."/customAction/".urlencode($customAction)."?valid");
  LStemplate :: assign('validation_label', _('Validate'));

  // List user available actions for this LSobject
  $LSview_actions = array(
    array(
      'label' => _('View'),
      'url' => "object/$LSobject/".urlencode($dn),
      'action' => 'view'
    ),
  );

  if (LSsession :: canEdit($LSobject, $dn)) {
    $LSview_actions[] = array(
      'label' => _('Modify'),
      'url' => "object/$LSobject/".urlencode($dn)."/modify",
      'action' => 'modify'
    );
  }
  LStemplate :: assign('LSview_actions',$LSview_actions);

  // Set & display template
  LSsession :: setTemplate('question.tpl');
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^object/(?P<LSobject>[^/]+)/(?P<dn>[^/]+)/customAction/(?P<customAction>[^/]+)/?$#', 'handle_LSobject_customAction');

/*
 * Handle old custom_action.php request for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_custom_action_php($request) {
  if (!isset($_GET['LSobject']) || !isset($_GET['dn']) || !isset($_GET['customAction']))
    $url = null;
  elseif (isset($_GET['valid']))
    $url = "object/".$_GET['LSobject']."/".$_GET['dn']."/customAction/".$_GET['customAction']."?valid";
  else
    $url = "object/".$_GET['LSobject']."/".$_GET['dn']."/customAction/".$_GET['customAction'];
  LSerror :: addErrorCode('LSsession_26', 'custom_action.php');
  LSlog :: warning(
    getFData(
      "Handling old URL style redirection:\n - Requested URL: '%{old}'\n - Redirection URL: '%{new}'\n - Referer: %{referer}",
      array (
        'old' => $request -> current_url,
        'new' => ($url?$url:'missing parameter to compose URL'),
        'referer' => ($request -> referer?"'".$request -> referer."'":'Unknown (direct access or hidden by web-browser)'),
      )
    )
  );
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^custom_action\.php#', 'handle_old_custom_action_php', false);

/*
 ************************************************************
 * LSaddon views
 ************************************************************
 */

/*
 * Handle LSaddon view request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_addon_view($request) {
  if (LSsession ::loadLSaddon($request -> LSaddon)) {
    if ( LSsession :: canAccessLSaddonView($request -> LSaddon, $request -> view) ) {
      LSsession :: showLSaddonView($request -> LSaddon, $request -> view);
    }
    else {
      LSerror :: addErrorCode('LSsession_11');
    }
  }
  // Print template
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^addon/(?P<LSaddon>[^/]+)/(?P<view>[^/]+)$#', 'handle_addon_view');

/*
 * Handle LSaddon view request old-URL for retro-compatibility
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_old_addon_view($request) {
 if ((isset($_GET['LSaddon'])) && (isset($_GET['view']))) {
   LSerror :: addErrorCode('LSsession_25', urldecode($_GET['LSaddon']));
   LSurl :: redirect('addon/'.$_GET['LSaddon'].'/'.$_GET['view']);
 }
 LSurl :: redirect();
}
LSurl :: add_handler('#^addon_view\.php#', 'handle_old_addon_view', false);

/*
 * API
 */

/*
 * LSobject API view helper to retreive LSobject from request
 *
 * Just a wrapper on get_LSobject_from_request() helper function to
 * correctly set parameters for API context.
 *
 * See get_LSobject_from_request() for details.
 *
 * @param[in] $request LSurlRequest The request
 * @param[in] $instanciate boolean Instanciate and return an object (optional, default: true)
 * @param[in] $check_access callable|null Permit to specify check access method (optional, default: see get_LSobject_from_request())
 *
 * @retval LSobject|boolean The instanciated LSobject (or True if $instanciate=false), or False
 *                          on error/access refused
 */
function get_LSobject_from_API_request($request, $instanciate=true, $check_access=null) {
  return get_LSobject_from_request($request, $instanciate, $check_access, true);
}

 /*
  * Handle API LSobject search
  *
  * @param[in] $request LSurlRequest The request
  *
  * @retval void
  **/
function handle_api_LSobject_search($request) {
  LSsession :: setAjaxDisplay();
  $object = get_LSobject_from_API_request($request);
  if (!$object)
    return;

  $LSobject = $object -> getType();

  if (!LSsession :: loadLSclass('LSsearch')) {
    LSsession :: addErrorCode('LSsession_05', 'LSsearch');
    LSsession :: displayAjaxReturn();
    return false;
  }

  // Instanciate a LSsearch
  $search = new LSsearch($LSobject, 'api', null, isset($_REQUEST['reset']));
  $search -> setParam('onlyAccessible', True);
  if (!$search -> setParamsFromRequest()) {
    LSsession :: displayAjaxReturn();
    return;
  }

  // Run search
  if (!$search -> run())
    LSlog :: fatal('Fail to run search.');

  $all = isset($_REQUEST['all']);

  if ($all) {
    $entries = $search -> listEntries();
    if (!is_array($entries))
      LSlog :: fatal("Fail to retreive search result");
  }
  else {
    // Retrieve page
    $page_nb = (isset($_REQUEST['page'])?(int)$_REQUEST['page']:0);
    $page = $search -> getPage($page_nb);

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
      LSlog :: fatal("Fail to retreive page #$page_nb.");
  }

  // Handle JSON output
  $data = array(
    'success' => true,
    'objects' => array(),
    'total' => $search -> total,
  );
  if (!$all) {
    $data['page'] = $page['nb'] + 1;
    $data['nbPages'] = $page['nbPages'];
  }
  foreach(($all?$entries:$page['list']) as $obj) {
    $data['objects'][$obj -> dn] = array(
      'name' => $obj -> displayName,
    );
    if ($search -> displaySubDn)
      $data['objects'][$obj -> dn][$search -> label_level] = $obj -> subDn;
    if ($search -> extraDisplayedColumns) {
      foreach ($search -> visibleExtraDisplayedColumns as $cid => $conf) {
        $data['objects'][$obj -> dn][$conf['label']] = $obj -> $cid;
      }
    }
    foreach ($search -> attributes as $attr) {
      if (LSsession :: canAccess($LSobject, $obj -> dn, 'r', $attr))
        $data['objects'][$obj -> dn][$attr] = $obj -> $attr;
    }
  }
  LSsession :: displayAjaxReturn($data);
}
LSurl :: add_handler('#^api/1.0/object/(?P<LSobject>[^/]+)/?$#', 'handle_api_LSobject_search', true, false, true);

/*
 * Handle API LSobject create request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_api_LSobject_create($request) {
  LSsession :: setAjaxDisplay();
  $object = get_LSobject_from_API_request(
    $request,
    true,                             // instanciate object
    array('LSsession', 'canCreate')   // Check access method
  );

  if (!$object)
    return;

  $data = array('success' => false);
  $LSobject = $object -> getType();
  $form = $object -> getForm('create', null, true); // Create form in API mode

  if (isset($_REQUEST['dataEntryForm'])) {
    $form -> applyDataEntryForm((string)$_REQUEST['dataEntryForm']);
  }
  $form -> setSubmited();

  if ($form->validate(true)) {
    // Data update for LDAP object
    if ($object -> updateData('create')) {
      $data['success'] = true;
      $data['type'] = $object -> getType();
      $data['dn'] = $object -> getDn();
      $data['name'] = $object -> getDisplayName();
      LSsession :: addInfo(_("Object has been added."));
    }
    else {
      $data['fields_errors'] = $form -> getErrors();
    }
  }
  else if ($form -> definedError()) {
    $data['fields_errors'] = $form -> getErrors();
  }
  LSsession :: displayAjaxReturn($data);
}
LSurl :: add_handler('#^api/1.0/object/(?P<LSobject>[^/]+)/create/?$#', 'handle_api_LSobject_create', true, false, true);

/*
 * Handle API LSobject show request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_api_LSobject_show($request) {
  LSsession :: setAjaxDisplay();
  $object = get_LSobject_from_API_request($request);
  if (!$object)
    return;

  $data = array(
    'success' => true,
    'dn' => $object -> getDn(),
    'type' => $object -> getType(),
    'name' => $object -> getDisplayName(),
    'details' => isset($_REQUEST['details']),
    'attributes' => array(),
    'relations' => array(),
  );

  $view = $object -> getView();
  foreach($view -> elements as $element) {
    $data['attributes'][$element -> name] = $element -> getApiValue($data['details']);
  }

  if (LSsession :: loadLSclass('LSrelation')) {
    foreach ($object -> getConfig('LSrelation', array(), 'array') as $rel_name => $rel_conf) {
      $data['relations'][$rel_name] = array();
      $relation = new LSrelation($object, $rel_name);
      $list = $relation -> listRelatedObjects();
      if (is_array($list)) {
        foreach($list as $o) {
          $data['relations'][$rel_name][$o -> getDn()] = $o -> getDisplayName(NULL,true);
        }
      }
      else {
        LSlog :: error("Fail to load related objects.");
      }
    }
  }
  LSsession :: displayAjaxReturn($data);
}
LSurl :: add_handler('#^api/1.0/object/(?P<LSobject>[^/]+)/?(?P<dn>[^/]+)/?$#', 'handle_api_LSobject_show', true, false, true);

/*
 * Handle API LSobject modify request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_api_LSobject_modify($request) {
  LSsession :: setAjaxDisplay();
  $object = get_LSobject_from_API_request(
    $request,
    true,                             // instanciate object
    array('LSsession', 'canEdit')     // Check access method
  );
  if (!$object)
    return;

  $data = array(
    'dn' => $object -> getDn(),
    'type' => $object -> getType(),
    'name' => $object -> getDisplayName(),
    'success' => false,
  );
  $form = $object -> getForm('modify', null, true);  // Create form in API mode
  $form -> setSubmited();

  if ($form->validate(true)) {
    // Update LDAP object data
    if ($object -> updateData('modify')) {
      // Update successful
      if (LSerror::errorsDefined()) {
        LSsession :: addInfo(_("The object has been partially modified."));
      }
      else {
        LSsession :: addInfo(_("The object has been modified successfully."));
        $data['success'] = true;
      }
    }
    elseif ($form -> definedError()) {
      $data['fields_errors'] = $form -> getErrors();
    }
  }
  else if ($form -> definedError()) {
    $data['fields_errors'] = $form -> getErrors();
  }
  LSsession :: displayAjaxReturn($data);
}
LSurl :: add_handler('#^api/1.0/object/(?P<LSobject>[^/]+)/(?P<dn>[^/]+)/modify/?$#', 'handle_api_LSobject_modify', true, false, true);

/*
 * Handle API LSobject remove request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_api_LSobject_remove($request) {
  LSsession :: setAjaxDisplay();
  $object = get_LSobject_from_API_request(
    $request,
    true,                             // instanciate object
    array('LSsession', 'canRemove')   // Check access method
  );
  if (!$object)
    return;

  $data = array(
   'dn' => $object -> getDn(),
   'type' => $object -> getType(),
   'name' => $object -> getDisplayName(),
   'success' => false,
  );

  // Remove object (if validated)
  if ($object -> remove()) {
    LSsession :: addInfo(getFData(_('%{objectname} has been successfully deleted.'), $data['name']));
    $data['success'] = true;
  }
  else {
    LSerror :: addErrorCode('LSldapObject_15', $objectname);
  }
  LSsession :: displayAjaxReturn($data);
}
LSurl :: add_handler('#^api/1.0/object/(?P<LSobject>[^/]+)/(?P<dn>[^/]+)/remove/?$#', 'handle_api_LSobject_remove', true, false, true);

/*
 * Handle API LSobject relation request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
**/
function handle_api_LSobject_relation($request) {
  LSsession :: setAjaxDisplay();
  $object = get_LSobject_from_API_request(
    $request,
    true  // instanciate object
  );
  if (!$object)
    return;

  $LSobject = $object -> getType();

  // Handle relation URL parameter
  $relationName = $request -> relation;
  if (!is_array($object -> getConfig("LSrelation.$relationName"))) {
    LSlog :: log_error("LSobject $LSobject have no relation '$relationName'.");
    LSsession :: displayAjaxReturn();
    return false;
  }

  // Check user access to this relation
  if (
    (isset($_REQUEST['add']) || isset($_REQUEST['remove'])) &&
    !LSsession :: relationCanEdit($object -> dn, $LSobject, $relationName)
  ) {
    LSerror :: addErrorCode('LSsession_11');
    LSsession :: displayAjaxReturn();
    return false;
  }

  // Load LSrelation PHP class (with warning)
  if (!LSsession :: loadLSclass('LSrelation', null, true)) {
    LSsession :: displayAjaxReturn();
    return false;
  }

  $relation = new LSrelation($object, $relationName);

  $data = array(
   'dn' => $object -> getDn(),
   'type' => $object -> getType(),
   'name' => $object -> getDisplayName(),
   'relation' => $relationName,
   'success' => false,
  );
  $warnings = array();

  // List current related objects
  $list = $relation -> listRelatedObjects();
  $listDns = array();
  if (is_array($list)) {
    foreach($list as $o) {
      $listDns[] = $o -> getDn();
    }
  }
  LSlog :: debug("Current related object(s): ".varDump($listDns));

  // Keep a copy of initial related objects list
  $initialListDns = $listDns;

  // Handle add
  $relatedLSobject = $object -> getConfig("LSrelation.$relationName.LSobject");
  $search = new LSsearch(
    $relatedLSobject,
    "LSrelation.api.$LSobject.$relationName",
    array(
      'scope' => 'base',
    )
  );
  if (isset($_REQUEST['add'])) {
    foreach (ensureIsArray($_REQUEST['add']) as $dn) {
      $dn = urldecode($dn);
      // Check if DN is already in relation
      if (in_array($dn, $listDns)) {
        LSlog :: debug("LSobject $relatedLSobject $dn is already in relation with ".$object -> getDn().".");
        continue;
      }

      // Check DN refer to a related object
      $search -> setParam('basedn', $dn);
      $search -> run(false);
      $result = $search -> listObjectsDn();
      if (!is_array($result) || count($result) != 1) {
        $warnings[] = "No $relatedLSobject found for DN $dn";
      }
      $listDns[] = $dn;
    }
  }

  if (isset($_REQUEST['remove'])) {
    // Handle remove
    foreach (ensureIsArray($_REQUEST['remove']) as $dn) {
      $dn = urldecode($dn);
      $found = false;
      while(true) {
        $key = array_search($dn, $listDns);
        if ($key === false) break;
        $found = true;
        unset($listDns[$key]);
      }
      if (!$found)
        LSlog :: debug("LSobject $relatedLSobject $dn is not in relation with ".$object -> getDn().".");
    }
  }

  // Add new related objects list in result
  $data['relatedObjects'] = array_values($listDns);

  if ($warnings) {
    LSerror :: addErrorCode(false, "Some problems detected on requested changes.");
    $data['warnings'] = $warnings;
  }
  else if ($initialListDns == $listDns) {
    LSsession :: addInfo('No changes done.');
    $data['success'] = true;
  }
  else {
    LSlog :: debug("New related object(s) list: ".varDump($listDns));
    if ($relation -> updateRelations($listDns)) {
      LSsession :: addInfo('Objects in relation updated.');
      $data['success'] = true;
    }
    else {
      LSerror :: addErrorCode(false, "Fail to update objects in relation");
    }
  }
  LSsession :: displayAjaxReturn($data);
}
LSurl :: add_handler('#^api/1.0/object/(?P<LSobject>[^/]+)/(?P<dn>[^/]+)/relation/(?P<relation>[^/]+)/?$#', 'handle_api_LSobject_relation', true, false, true);
