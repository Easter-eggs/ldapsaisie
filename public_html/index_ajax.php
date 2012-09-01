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
  $class = $_REQUEST['template'];
  if (LSsession :: loadLSclass($class)) {
    $meth = 'ajax_'.$_REQUEST['action'];
    if (method_exists($class,$meth)) {
       call_user_func(array($class,$meth),$data);
    }
  }
}

LSsession :: displayAjaxReturn($data);

?>
