<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle}{/if}</title>
    <link rel="stylesheet" type="text/css" href="templates/css/login.css" media="screen" title="Normal" />
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
<div class='loginform'>
<img src='templates/images/logo.png' alt='Logo' id='loginform_logo' />
<div id='loading_zone'></div>
<form action='{$loginform_action}' method='post'>
<dl class='loginform'>
  <dt {$loginform_ldapserver_style}>{$loginform_label_ldapserver}</dt>
  <dd {$loginform_ldapserver_style}>
    <select name='LSsession_ldapserver' id='LSsession_ldapserver'>{html_options values=$loginform_ldapservers_index output=$loginform_ldapservers_name selected=$ldapServerId}</select>
  </dd>
  <dt class='loginform-level' {$loginform_ldapserver_style}>{$loginform_label_level}</dt>
  <dd class='loginform-level' {$loginform_ldapserver_style}><select name='LSsession_topDn' id='LSsession_topDn'>{html_options values=$loginform_topdn_index output=$loginform_topdn_name selected=$topDn}</select></dd>
  <dt>{$loginform_label_user}</dt>
  <dd><input type='text' name='LSsession_user' /></dd>
  <dt>{$loginform_label_pwd}</dt>
  <dd><input type='password' name='LSsession_pwd' /></dd>
  <dd><input type='submit' value='{$loginform_label_submit}' /></dd>
</dl>
</form>
</div>
</body>
</html>
