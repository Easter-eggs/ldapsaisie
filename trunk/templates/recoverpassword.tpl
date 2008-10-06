<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle}{/if}</title>
    <link rel="stylesheet" type="text/css" href="templates/css/recoverpassword.css" media="screen" title="Normal" />
    {$LSsession_css}
    {$LSsession_js}
  </head>
<body>
<div id='LSerror'>
{$LSerrors}
</div>
<div id='LSdebug'>
  <a href='#' id='LSdebug_hidden'>X</a> 
  <div id='LSdebug_infos'>{if $LSdebug != ''}{$LSdebug}{/if}</div>
</div>
<div class='recoverpasswordform'>
<img src='templates/images/logo.png' alt='Logo' id='recoverpasswordform_logo' />
<div id='loading_zone'></div>
<form action='{$recoverpasswordform_action}' method='post'>
<dl class='recoverpasswordform'>
  <dt {$recoverpasswordform_ldapserver_style}>{$recoverpasswordform_label_ldapserver}</dt>
  <dd {$recoverpasswordform_ldapserver_style}>
    <select name='LSsession_ldapserver' id='LSsession_ldapserver'>{html_options values=$recoverpasswordform_ldapservers_index output=$recoverpasswordform_ldapservers_name selected=$ldapServerId}</select>
  </dd>
  <dt>{$recoverpasswordform_label_user}</dt>
  <dd><input type='text' name='LSsession_user' /></dd>
  <dd><input type='submit' value='{$recoverpasswordform_label_submit}' /></dd>
</dl>
</form>

<p id='recoverpassword_msg'>{$recoverpassword_msg}</p>

<a href='index.php' id='recoverpassword_back'>{$recoverpasswordform_label_back}</a>
</div>
</body>
</html>