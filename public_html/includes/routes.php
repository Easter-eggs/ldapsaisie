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
  LSsession :: setTemplate('accueil.tpl');

  // Display template
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^(index\.php)?$#', 'handle_index', true);

/*
 * Handle image request
 *
 * @param[in] $request LSurlRequest The request
 *
 * @retval void
 **/
function handle_image($request) {
  $img_path = LStemplate :: getImagePath($request -> image);
  if (is_file($img_path)) {
   dumpFile($img_path);
  }
  LSurl :: error_404($request);
}
LSurl :: add_handler('#^image/(?P<image>[^/]+)$#', 'handle_image', false);

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
 *
 * @retval LSobject|boolean The instanciated LSobject (or True if $instanciate=false), or False
 *                          on error/access refused
 */
function get_LSobject_from_request($request, $instanciate=true, $check_access=null) {
    $LSobject = $request -> LSobject;
    $dn = (isset($request -> dn)?$request -> dn:null);

    // Handle $check_access parameter
    if (is_null($check_access))
      $check_access = array('LSsession', 'canAccess');

    // Handle SELF redirect
    if ( $LSobject == 'SELF' ) {
      $LSobject = LSsession :: getLSuserObject() -> getType();
      $dn = LSsession :: getLSuserObjectDn();
      LSurl :: redirect("object/$LSobject/".urlencode($dn));
    }

    // If $dn, check user access to this LSobject
    if ($dn) {
      if (!call_user_func($check_access, $LSobject, $dn)) {
        LSerror :: addErrorCode('LSsession_11');
        return false;
      }
    }
    else if (!LSsession :: in_menu($LSobject) && !call_user_func($check_access, $LSobject)) {
      LSerror :: addErrorCode('LSsession_11');
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
    LSsession :: addErrorCode('LSsession_05','LSsearch');
    return false;
  }

  // Set pagetitle
  LStemplate :: assign('pagetitle', $object -> getLabel());

  // Instanciate a LSsearch
  $LSsearch = new LSsearch($LSobject, 'LSview', null, (isset($_REQUEST['reset'])));
  $LSsearch -> setParam('extraDisplayedColumns', True);
  $LSsearch -> setParamsFormPostData();

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
      'url' => 'import.php?LSobject='.$LSobject,
      'action' => 'import'
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
          'hideLabel' => ((isset($config['hideLabel']))?$config['hideLabel']:False),
          'helpInfo' => ((isset($config['helpInfo']))?__($config['helpInfo']):False),
          'url' => 'custom_search_action.php?LSobject='.$LSobject.'&amp;customAction='.$name,
          'action' => ((isset($config['icon']))?$config['icon']:'generate'),
          'class' => 'LScustomActions'.(($config['noConfirmation'])?' LScustomActions_noConfirmation':'')
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
    LSform :: loadDependenciesDisplayView();
  }

  // Set & display template
  LSsession :: setTemplate('viewSearch.tpl');
  LSsession :: displayTemplate();
}
LSurl :: add_handler('#^object/(?P<LSobject>[^/]+)/?$#', 'handle_LSobject_search');

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
          $relation = new LSrelation($obj, $_GET['LSrelation']);
          if ($relation -> exists()) {
            $attr = $relation -> getRelatedEditableAttribute();
            if (isset($object -> attrs[$attr])) {
              $value = $relation -> getRelatedKeyValue();
              if (is_array($value)) $value=$value[0];
              $object -> attrs[$attr] -> data = array($value);
            }
            else {
              LSerror :: addErrorCode('LSrelations_06',array('relation' => $relation -> getName(),'LSobject' => $obj -> getType()));
            }
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
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^create.php#', 'handle_old_create_php');

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
          'hideLabel' => ((isset($config['hideLabel']))?$config['hideLabel']:False),
          'helpInfo' => ((isset($config['helpInfo']))?__($config['helpInfo']):False),
          'url' => 'custom_action.php?LSobject='.$LSobject.'&amp;dn='.urlencode($dn).'&amp;customAction='.$name,
          'action' => ((isset($config['icon']))?$config['icon']:'generate'),
          'class' => 'LScustomActions'.(($config['noConfirmation'])?' LScustomActions_noConfirmation':'')
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
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^view.php#', 'handle_old_view_php');

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
            'LSredirect' => "object/$LSobject/".$object -> getDn()
          )
        );
        return true;
      }
      else {
        if (!LSdebugDefined()) {
          LSurl :: redirect("object/$LSobject/".$object -> getDn());
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
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^modify.php#', 'handle_old_modify_php');

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
  LSurl :: redirect($url);
}
LSurl :: add_handler('#^remove.php#', 'handle_old_remove_php');

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
      // Print template
      LSsession :: displayTemplate();
    }
    else {
      LSerror :: addErrorCode('LSsession_11');
    }
  }
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
   LSsession :: redirect('addon/'.$_GET['LSaddon'].'/'.$_GET['view']);
 }
 LSsession :: redirect();
}
LSurl :: add_handler('#^addon_view.php#', 'handle_old_addon_view');
