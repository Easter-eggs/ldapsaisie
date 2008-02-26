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
$data=NULL;
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
              $data = array('list_topDn' => $list, 'imgload' => $_REQUEST['imgload']);
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
      break;
      case 'refreshField':
        if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['idform'])) && (isset($_REQUEST['imgload'])) ) {
          $object = new $_REQUEST['objecttype']();
          //$object -> loadData($_REQUEST['objectdn']);
          $form = $object -> getForm($_REQUEST['idform']);
          $field=$form -> getElement($_REQUEST['attribute']);
          $val = $field -> getDisplay(true);
          if ( $val ) {
            $data = array(
              'html'    => $val['html'],
              'imgload' => $_REQUEST['imgload']
            );
          }
          else {
            $data = array(
              'LSerror' => $GLOBALS['LSerror']->getErrors(),
              'imgload' => $_REQUEST['imgload']
              );
          }
        }
      break;
    }
  break;
  case 'LSrelation':
    switch($_REQUEST['action']) {
      case 'refreshSession':
        if ((isset($_REQUEST['id'])) && (isset($_REQUEST['href'])) && (isset($_REQUEST['imgload']))) {
          if (isset($_SESSION['LSrelation'][$_REQUEST['id']])) {
            $conf = $_SESSION['LSrelation'][$_REQUEST['id']];
            if ($GLOBALS['LSsession']->loadLSobject($conf['objectType'])) {
              $object = new $conf['objectType']();
              if (($object -> loadData($conf['objectDn'])) && (isset($object->config['relations'][$conf['relationName']]))) {
                $relationConf = $object->config['relations'][$conf['relationName']];
                if ($GLOBALS['LSsession'] -> relationCanEdit($object -> getValue('dn'),$conf['relationName'])) {
                  if (method_exists($relationConf['LSobject'],$relationConf['list_function'])) {
                    $objRel = new $relationConf['LSobject']();
                    $list = $objRel -> $relationConf['list_function']($object);
                    $_SESSION['LSselect'][$relationConf['LSobject']]=array();
                    if (is_array($list)) {
                      foreach($list as $o) {
                        $_SESSION['LSselect'][$relationConf['LSobject']][] = $o -> getDn();
                      }
                    }
                    $data = array(
                      'href' => $_REQUEST['href'],
                      'id' => $_REQUEST['id']
                    );
                  }
                  else {
                    $GLOBALS['LSerror'] -> addErrorCode(1013,$relationName);
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
              $GLOBALS['LSerror'] -> addErrorCode(1012);
            }
            $data['imgload'] = $_REQUEST['imgload'];
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode(1012);
          }
        }
      break;
      case 'refreshList':
        if ((isset($_REQUEST['id'])) && (isset($_REQUEST['imgload']))) {
          if (isset($_SESSION['LSrelation'][$_REQUEST['id']])) {
            $conf = $_SESSION['LSrelation'][$_REQUEST['id']];
            if ($GLOBALS['LSsession']->loadLSobject($conf['objectType'])) {
              $object = new $conf['objectType']();
              if (($object -> loadData($conf['objectDn'])) && (isset($object->config['relations'][$conf['relationName']]))) {
                $relationConf = $object->config['relations'][$conf['relationName']];
                if ($GLOBALS['LSsession'] -> relationCanEdit($object -> getValue('dn'),$conf['relationName'])) {
                  if (is_array($_SESSION['LSselect'][$relationConf['LSobject']])) {
                    if (method_exists($relationConf['LSobject'],$relationConf['update_function'])) {
                      $objRel = new $relationConf['LSobject']();
                      if($objRel -> $relationConf['update_function']($object,$_SESSION['LSselect'][$relationConf['LSobject']])) {
                        if (method_exists($relationConf['LSobject'],$relationConf['list_function'])) {
                          $list = $objRel -> $relationConf['list_function']($object);
                          if (is_array($list)) {
                            foreach($list as $o) {
                              $data['html'].= "<li class='LSrelation'>".$o -> getDisplayValue()."</li>\n";
                            }
                          }
                          else {
                            $data['html'] = "<li>"._('Liste vide.')."</li>\n";
                          }
                          $data['id'] = $_REQUEST['id'];
                        }
                        else {
                          $GLOBALS['LSerror'] -> addErrorCode(1013,$relationName);
                        }
                      }
                      else {
                        $GLOBALS['LSerror'] -> addErrorCode(1015,$relationName);
                      }
                    }
                    else {
                      $GLOBALS['LSerror'] -> addErrorCode(1014,$relationName);
                    }
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
              $GLOBALS['LSerror'] -> addErrorCode(1012);
            }
            $data['imgload'] = $_REQUEST['imgload'];
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode(1012);
          }
        }
      break;
      case 'deleteByDisplayValue':
        if ((isset($_REQUEST['id'])) && (isset($_REQUEST['value'])) && (isset($_REQUEST['imgload']))) {
          if (isset($_SESSION['LSrelation'][$_REQUEST['id']])) {
            $conf = $_SESSION['LSrelation'][$_REQUEST['id']];
            if ($GLOBALS['LSsession']->loadLSobject($conf['objectType'])) {
              $object = new $conf['objectType']();
              if (($object -> loadData($conf['objectDn'])) && (isset($object->config['relations'][$conf['relationName']]))) {
                $relationConf = $object->config['relations'][$conf['relationName']];
                if ($GLOBALS['LSsession'] -> relationCanEdit($object -> getValue('dn'),$conf['relationName'])) {
                  if (method_exists($relationConf['LSobject'],$relationConf['list_function'])) {
                    $objRel = new $relationConf['LSobject']();
                    $list = $objRel -> $relationConf['list_function']($object);
                    if (is_array($list)) {
                      $ok=false;
                      foreach($list as $o) {
                        if($o -> getDisplayValue() == $_REQUEST['value']) {
                          if (!$o -> deleteOneMember($object)) {
                            $GLOBALS['LSerror'] -> addErrorCode(1015,$conf['relationName']);
                          }
                          else {
                            $ok = true;
                          }
                        }
                      }
                      if (!$ok) {
                        $GLOBALS['LSerror'] -> addErrorCode(1015,$conf['relationName']);
                      }
                    }
                    else {
                      $GLOBALS['LSerror'] -> addErrorCode(1015,$conf['relationName']);
                      $GLOBALS['LSerror'] -> addErrorCode(1);
                    }
                  }
                  else {
                    $GLOBALS['LSerror'] -> addErrorCode(1013,$conf['relationName']);
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
              $GLOBALS['LSerror'] -> addErrorCode(1012);
            }
            $data['imgload'] = $_REQUEST['imgload'];
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode(1012);
          }
        }
      break;
    }
  break;
  case 'LSselect':
    switch($_REQUEST['action']) {
      case 'addLSselectobject-item':
        if ((isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn']))) {
          if (is_array($_SESSION['LSselect'][$_REQUEST['objecttype']])) {
            if (!in_array($_REQUEST['objectdn'],$_SESSION['LSselect'][$_REQUEST['objecttype']])) {
              $_SESSION['LSselect'][$_REQUEST['objecttype']][]=$_REQUEST['objectdn'];
            }
          }
          else {
            $_SESSION['LSselect'][$_REQUEST['objecttype']][]=$_REQUEST['objectdn'];
          }
        }
        $data=$_REQUEST['imgload'];
      break;
      case 'dropLSselectobject-item':
        if ((isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn']))) {
          if (is_array($_SESSION['LSselect'][$_REQUEST['objecttype']])) {
            $result=array();
            foreach ($_SESSION['LSselect'][$_REQUEST['objecttype']] as $val) {
              if ($val!=$_REQUEST['objectdn']) {
                $result[]=$val;
              }
            }
            $_SESSION['LSselect'][$_REQUEST['objecttype']]=$result;
          }
        }
        $data=$_REQUEST['imgload'];
      break;
      case 'refreshSession':
        if ((isset($_REQUEST['objecttype'])) && (isset($_REQUEST['values'])) && (isset($_REQUEST['imgload'])) && (isset($_REQUEST['href'])) ) {
          $_SESSION['LSselect'][$_REQUEST['objecttype']]=array();
          $values=json_decode($_REQUEST['values'],false);
          if (is_array($values)) {
            foreach($values as $val) {
              $_SESSION['LSselect'][$_REQUEST['objecttype']][]=$val;
            }
          }
          $data=array(
            'imgload' => $_REQUEST['imgload'],
            'href' => $_REQUEST['href'],
            'values' => $values
          );
        }
        else {
          $GLOBALS['LSerror'] -> addErrorCode(1012);
          $data = array(
            'LSerror' => $GLOBALS['LSerror']->getErrors(),
            'imgload' => $_REQUEST['imgload']
          );
        }
      break;
    }
  break;
}

if ($GLOBALS['LSerror']->errorsDefined()) {
  $data['LSerror'] = $GLOBALS['LSerror']->getErrors();
}

echo json_encode($data);
?>
