<?php

require_once 'core.php';

if (!isset($_REQUEST['noLSsession'])) {
  if ( !LSsession :: startLSsession() ) {
    LSerror :: addErrorCode('LSsession_22');
    $_ERRORS = true;
  }
}
else {
  LSsession :: initialize() or die('Error during initialization.');
}

$data=NULL;
if (!isset($_ERRORS)) {
  if (isset($_REQUEST['template'])) {
    $class = $_REQUEST['template'];
    if (LSsession :: loadLSclass($class)) {
      $meth = 'ajax_'.$_REQUEST['action'];
      if (method_exists($class,$meth)) {
         $class :: $meth($data);
      }
    }
  }
  elseif (isset($_REQUEST['addon'])) {
    $addon = $_REQUEST['addon'];
    if (LSsession :: loadLSaddon($addon)) {
      $func = 'ajax_'.$_REQUEST['action'];
      if (function_exists($func)) {
        $func = new ReflectionFunction($func);
        if (basename($func->getFileName())=="LSaddons.$addon.php") {
          $func->invokeArgs(array(&$data));
        }
        else {
          LSerror :: addErrorCode('LSsession_21',array('func' => $func -> getName(),'addon' => $addon));
        }
      }
    }
  }
}

LSsession :: displayAjaxReturn($data);

