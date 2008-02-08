<?php

require_once 'includes/functions.php';
require_once 'includes/class/class.LSsession.php';

$GLOBALS['LSsession'] = new LSsession();
$GLOBALS['LSsession'] -> loadLSobjects();

if ($_REQUEST['template'] != 'login') {
  if ( !$GLOBALS['LSsession'] -> startLSsession() ) {
    echo json_encode(array('LSerror' => 'LSsession : Impossible d\'initialiser la LSsession.' ));
  }
}
switch($_REQUEST['template']) {
  case 'login':
    switch($_REQUEST['action']) {
      case 'onLdapServerChanged':
        if ( isset($_REQUEST['server']) ) {
          $GLOBALS['LSsession'] -> setLdapServer($_REQUEST['server']);
          if ( $GLOBALS['LSsession'] -> LSldapConnect() ) {
            $list = $GLOBALS['LSsession'] -> getSubDnLdapServerOptions();
            if (is_string($list)) {
              $list="<select name='LSsession_topDn' id='LSsession_topDn'>".$list."</select>";
              $data = array('list_topDn' => $list);
            }
            else if (is_array($list)){
              $data = array('LSerror' => $GLOBALS['LSerror']->getErrors());
            }
            else {
              $data = null;
            }
          }
          else {
            $data = array('LSerror' => $GLOBALS['LSerror']->getErrors());
          }
        }
        else {
          $data=NULL;
        }
      break;
    }
  break;
  case 'LSform':
    switch($_REQUEST['action']) {
      case 'onAddFieldBtnClick':
        if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['idform'])) && (isset($_REQUEST['img'])) ) {
          $object = new $_REQUEST['objecttype']();
          $object -> loadData($_REQUEST['objectdn']);
          $form = $object -> getForm($_REQUEST['idform']);
          $emptyField=$form -> getEmptyField($_REQUEST['attribute']);
          if ( $emptyField ) {
            $data = array(
              'html' => $form -> getEmptyField($_REQUEST['attribute']),
              'img' => $_REQUEST['img'],
            );
          }
          else {
            $data = array('LSerror' => $GLOBALS['LSerror']->getErrors());
          }
        }
        else {
          $data=NULL;
        }
      break;
    }
  break;
}

echo json_encode($data);
?>
