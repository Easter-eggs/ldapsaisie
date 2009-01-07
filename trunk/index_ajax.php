<?php

require_once 'includes/functions.php';
require_once 'includes/class/class.LSsession.php';

$GLOBALS['LSsession'] = new LSsession();

if (($_REQUEST['template'] != 'login')&&($_REQUEST['template'] != 'recoverPassword')) {
  if ( !$GLOBALS['LSsession'] -> startLSsession() ) {
    $GLOBALS['LSerror'] -> addErrorCode('LSsession_22');
    $_ERRORS = true;
  }
}
$data=NULL;
if (!isset($_ERRORS)) {
  switch($_REQUEST['template']) {
    case 'login':
      switch($_REQUEST['action']) {
        case 'onLdapServerChanged':
          if ( isset($_REQUEST['server']) ) {
            $GLOBALS['LSsession'] -> setLdapServer($_REQUEST['server']);
            $data = array();
            if ( $GLOBALS['LSsession'] -> LSldapConnect() ) {
              session_start();
              if (isset($_SESSION['LSsession_topDn'])) {
                $sel = $_SESSION['LSsession_topDn'];
              }
              else {
                $sel = NULL;
              }
              $list = $GLOBALS['LSsession'] -> getSubDnLdapServerOptions($sel);
              if (is_string($list)) {
                $data['list_topDn'] = "<select name='LSsession_topDn' id='LSsession_topDn'>".$list."</select>";
                $data['subDnLabel'] = $GLOBALS['LSsession'] -> getSubDnLabel();
              }
            }
            $data['recoverPassword'] = isset($GLOBALS['LSsession'] -> ldapServer['recoverPassword']);
          }
        break;
      }
    break;
    case 'recoverPassword':
      switch($_REQUEST['action']) {
        case 'onLdapServerChanged':
          if ( isset($_REQUEST['server']) ) {
            $GLOBALS['LSsession'] -> setLdapServer($_REQUEST['server']);
            $data=array('recoverPassword' => isset($GLOBALS['LSsession'] -> ldapServer['recoverPassword']));
          }
        break;
      }
    break;
    case 'LSform':
      switch($_REQUEST['action']) {
        case 'onAddFieldBtnClick':
          if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['idform'])) && (isset($_REQUEST['fieldId'])) ) {
            if ($GLOBALS['LSsession'] -> loadLSobject($_REQUEST['objecttype'])) {
              $object = new $_REQUEST['objecttype']();
              $object -> loadData($_REQUEST['objectdn']);
              $form = $object -> getForm($_REQUEST['idform']);
              $emptyField=$form -> getEmptyField($_REQUEST['attribute']);
              if ( $emptyField ) {
                $data = array(
                  'html' => $form -> getEmptyField($_REQUEST['attribute']),
                  'fieldId' => $_REQUEST['fieldId'],
                  'fieldtype' => get_class($form -> getElement($_REQUEST['attribute']))
                );
              }
            }
          }
        break;
        case 'LSformElement_select_object_refresh':
          if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['idform'])) ) {
            if ($GLOBALS['LSsession'] -> loadLSobject($_REQUEST['objecttype'])) {
              $object = new $_REQUEST['objecttype']();
              $form = $object -> getForm($_REQUEST['idform']);
              $field=$form -> getElement($_REQUEST['attribute']);
              $val = $field -> getValuesFromSession();
              if ( $val ) {
                $data = array(
                  'objects'    => $val
                );
              }
            }
          }
        break;
        case 'LSformElement_select_object_searchAdd':
          if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['pattern'])) && (isset($_REQUEST['idform'])) ) {
            if ($GLOBALS['LSsession'] -> loadLSobject($_REQUEST['objecttype'])) {
              $object = new $_REQUEST['objecttype']();
              $form = $object -> getForm($_REQUEST['idform']);
              $field=$form -> getElement($_REQUEST['attribute']);
              $data['objects'] = $field -> searchAdd($_REQUEST['pattern']);
            }
          }
        break;
        case 'generatePassword':
          if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['idform'])) ) {
            if ($GLOBALS['LSsession'] -> loadLSobject($_REQUEST['objecttype'])) {
              $object = new $_REQUEST['objecttype']();
              if ($object) {
                $form = $object -> getForm($_REQUEST['idform']);
                if ($form) {
                  $field=$form -> getElement($_REQUEST['attribute']);
                  if ($field) {
                    $val = $field -> generatePassword();
                    if ( $val ) {
                      $data = array(
                        'generatePassword' => $val
                      );
                    }
                  }
                }
              }
            }
          }
        break;
        case 'verifyPassword':
          if ((isset($_REQUEST['attribute'])) && (isset($_REQUEST['objecttype'])) && (isset($_REQUEST['fieldValue'])) && (isset($_REQUEST['idform'])) && (isset($_REQUEST['objectdn'])) ) {
            if ($GLOBALS['LSsession'] -> loadLSobject($_REQUEST['objecttype'])) {
              $object = new $_REQUEST['objecttype']();
              $form = $object -> getForm($_REQUEST['idform']);
              $object -> loadData($_REQUEST['objectdn']);
              $field=$form -> getElement($_REQUEST['attribute']);
              $val = $field -> verifyPassword($_REQUEST['fieldValue']);
              $data = array(
                'verifyPassword' => $val
              );
            }
          }
        break;
      }
    break;
    case 'LSrelation':
      switch($_REQUEST['action']) {
        case 'refreshSession':
          if ((isset($_REQUEST['id'])) && (isset($_REQUEST['href'])) ) {
            if (isset($_SESSION['LSrelation'][$_REQUEST['id']])) {
              $conf = $_SESSION['LSrelation'][$_REQUEST['id']];
              if ($GLOBALS['LSsession']->loadLSobject($conf['objectType'])) {
                $object = new $conf['objectType']();
                if (($object -> loadData($conf['objectDn'])) && (isset($object->config['relations'][$conf['relationName']]))) {
                  $relationConf = $object->config['relations'][$conf['relationName']];
                  if ($GLOBALS['LSsession'] -> loadLSobject($relationConf['LSobject'])) {
                    if ($GLOBALS['LSsession'] -> relationCanEdit($object -> getValue('dn'),$object -> getType(),$conf['relationName'])) {
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
                        $GLOBALS['LSerror'] -> addErrorCode('LSrelations_01',$relationName);
                      }
                    }
                    else {
                      $GLOBALS['LSerror'] -> addErrorCode('LSsession_11');
                    }
                  }
                }
                else {
                  $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
                }
              }
              else {
                $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
              }
            }
            else {
              $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
            }
          }
        break;
        case 'refreshList':
          if (isset($_REQUEST['id'])) {
            if (isset($_SESSION['LSrelation'][$_REQUEST['id']])) {
              $conf = $_SESSION['LSrelation'][$_REQUEST['id']];
              if ($GLOBALS['LSsession']->loadLSobject($conf['objectType'])) {
                $object = new $conf['objectType']();
                if (($object -> loadData($conf['objectDn'])) && (isset($object->config['relations'][$conf['relationName']]))) {
                  $relationConf = $object->config['relations'][$conf['relationName']];
                  if ($GLOBALS['LSsession'] -> loadLSobject($relationConf['LSobject'])) {
                    if ($GLOBALS['LSsession'] -> relationCanEdit($object -> getValue('dn'),$object -> getType(),$conf['relationName'])) {
                      if (is_array($_SESSION['LSselect'][$relationConf['LSobject']])) {
                        if (method_exists($relationConf['LSobject'],$relationConf['update_function'])) {
                          $objRel = new $relationConf['LSobject']();
                          if($objRel -> $relationConf['update_function']($object,$_SESSION['LSselect'][$relationConf['LSobject']])) {
                            if (method_exists($relationConf['LSobject'],$relationConf['list_function'])) {
                              $list = $objRel -> $relationConf['list_function']($object);
                              if (is_array($list)&&(!empty($list))) {
                                foreach($list as $o) {
                                  $data['html'].= "<li class='LSrelation'><a href='view.php?LSobject=".$relationConf['LSobject']."&amp;dn=".$o -> getDn()."' class='LSrelation' id='".$o -> getDn()."'>".$o -> getDisplayName(NULL,true)."</a></li>\n";
                                }
                              }
                              else {
                                if (isset($relationConf['emptyText'])) {
                                  $data['html'] = "<li>".$relationConf['emptyText']."</li>\n";
                                }
                                else {
                                  $data['html'] = "<li>"._('Aucun objet en relation.')."</li>\n";
                                }
                              }
                              $data['id'] = $_REQUEST['id'];
                            }
                            else {
                              $GLOBALS['LSerror'] -> addErrorCode('LSrelations_01',$relationName);
                            }
                          }
                          else {
                            $GLOBALS['LSerror'] -> addErrorCode('LSrelations_03',$relationName);
                          }
                        }
                        else {
                          $GLOBALS['LSerror'] -> addErrorCode('LSrelations_02',$relationName);
                        }
                      }
                    }
                    else {
                      $GLOBALS['LSerror'] -> addErrorCode('LSsession_11');
                    }
                  }
                }
                else {
                  $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
                }
              }
              else {
                $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
              }
            }
            else {
              $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
            }
          }
        break;
        case 'deleteByDn':
          if ((isset($_REQUEST['id'])) && (isset($_REQUEST['dn']))) {
            if (isset($_SESSION['LSrelation'][$_REQUEST['id']])) {
              $conf = $_SESSION['LSrelation'][$_REQUEST['id']];
              if ($GLOBALS['LSsession']->loadLSobject($conf['objectType'])) {
                $object = new $conf['objectType']();
                if (($object -> loadData($conf['objectDn'])) && (isset($object->config['relations'][$conf['relationName']]))) {
                  $relationConf = $object->config['relations'][$conf['relationName']];
                  if ($GLOBALS['LSsession'] -> loadLSobject($relationConf['LSobject'])) {
                    if ($GLOBALS['LSsession'] -> relationCanEdit($object -> getValue('dn'),$object -> getType(),$conf['relationName'])) {
                      if (method_exists($relationConf['LSobject'],$relationConf['list_function'])) {
                        $objRel = new $relationConf['LSobject']();
                        $list = $objRel -> $relationConf['list_function']($object);
                        if (is_array($list)) {
                          $ok=false;
                          foreach($list as $o) {
                            if($o -> getDn() == $_REQUEST['dn']) {
                              if (!$o -> $relationConf['remove_function']($object)) {
                                $GLOBALS['LSerror'] -> addErrorCode('LSrelations_03',$conf['relationName']);
                              }
                              else {
                                $ok = true;
                              }
                            }
                          }
                          if (!$ok) {
                            LSdebug($_REQUEST['value']." introuvé parmi la liste");
                            $GLOBALS['LSerror'] -> addErrorCode('LSrelations_03',$conf['relationName']);
                          }
                          else {
                            $data=array(
                              'dn' => $_REQUEST['dn']
                            );
                          }
                        }
                        else {
                          $GLOBALS['LSerror'] -> addErrorCode('LSrelations_03',$conf['relationName']);
                        }
                      }
                      else {
                        $GLOBALS['LSerror'] -> addErrorCode('LSrelations_01',$conf['relationName']);
                      }
                    }
                    else {
                      $GLOBALS['LSerror'] -> addErrorCode('LSsession_11');
                    }
                  }
                }
                else {
                  $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
                }
              }
              else {
                $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
              }
            }
            else {
              $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
            }
          }
        break;
      }
    break;
    case 'LSselect':
      switch($_REQUEST['action']) {
        case 'addLSselectobject-item':
          if ((isset($_REQUEST['objecttype'])) && (isset($_REQUEST['objectdn'])) && (isset($_REQUEST['multiple']))) {
            if (!$_REQUEST['multiple']) {
              $_SESSION['LSselect'][$_REQUEST['objecttype']]=array($_REQUEST['objectdn']);
            }
            else if (is_array($_SESSION['LSselect'][$_REQUEST['objecttype']])) {
              if (!in_array($_REQUEST['objectdn'],$_SESSION['LSselect'][$_REQUEST['objecttype']])) {
                $_SESSION['LSselect'][$_REQUEST['objecttype']][]=$_REQUEST['objectdn'];
              }
            }
            else {
              $_SESSION['LSselect'][$_REQUEST['objecttype']][]=$_REQUEST['objectdn'];
            }
          }
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
        break;
        case 'refreshSession':
          if ((isset($_REQUEST['objecttype'])) && (isset($_REQUEST['values'])) ) {
            $_SESSION['LSselect'][$_REQUEST['objecttype']]=array();
            $values=json_decode($_REQUEST['values'],false);
            if (is_array($values)) {
              foreach($values as $val) {
                $_SESSION['LSselect'][$_REQUEST['objecttype']][]=$val;
              }
            }
            $data=array(
              'values' => $values
            );
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
          }
        break;
      }
    break;
    case 'LSmail':
      switch($_REQUEST['action']) {
        case 'display':
          if (isset($_REQUEST['object']['type']) && isset($_REQUEST['object']['dn'])) {
            if ($GLOBALS['LSsession']->loadLSobject($_REQUEST['object']['type'])) {
              $obj = new $_REQUEST['object']['type']();
              $obj -> loadData($_REQUEST['object']['dn']);
              $msg = $obj -> getFData($_REQUEST['msg']);
              $subject = $obj -> getFData($_REQUEST['subject']);
            }
          }
          else {
            $msg = $_REQUEST['msg'];
            $subject = $_REQUEST['subject'];
          }
          
          $GLOBALS['Smarty'] -> assign('LSmail_msg',$msg);
          $GLOBALS['Smarty'] -> assign('LSmail_subject',$subject);
          if (is_array($_REQUEST['mails'])) {
            $GLOBALS['Smarty'] -> assign('LSmail_mails',$_REQUEST['mails']);
          }
          else if(empty($_REQUEST['mails'])) {
            $GLOBALS['Smarty'] -> assign('LSmail_mails',array($_REQUEST['mails']));
          }
          $GLOBALS['Smarty'] -> assign('LSmail_mail_label',_('E-mail'));
          $GLOBALS['Smarty'] -> assign('LSmail_subject_label',_('Sujet'));
          $GLOBALS['Smarty'] -> assign('LSmail_msg_label',_('Message'));
          
          $data = array(
            'html' => $GLOBALS['Smarty'] -> fetch('LSmail.tpl')
          );
        break;
        case 'send':
          if (isset($_REQUEST['infos'])) {
            if ($GLOBALS['LSsession'] -> loadLSaddon('mail')) {
              if(sendMail($_REQUEST['infos']['mail'],$_REQUEST['infos']['subject'],$_REQUEST['infos']['msg'])) {
                $data = array(
                  'msgok' => _("Votre message a bien été envoyé.")
                );
              }
            }
          }
          else {
            $GLOBALS['LSerror'] -> addErrorCode('LSsession_12');
          }
      }
    break;
  }
}

$GLOBALS['LSsession'] -> displayAjaxReturn($data);

?>
