<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle|escape:"htmlall"}{/if}</title>
    <base href="{$public_root_url}/"/>
    <link rel="stylesheet" type="text/css" href="{css name='recoverpassword.css'}" media="screen" title="Normal" />
    {$LSsession_css}
    {$LSsession_js}
  </head>
<body>

{include file='ls:LSdefault.tpl'}

<div class='recoverpasswordform'>
<img src='{img name='logo'}' alt='Logo' id='recoverpasswordform_logo' />
<div id='loading_zone'></div>
{if $recoverpassword_step == 'start'}
<form action='{$recoverpasswordform_action}' method='post'>
<dl class='recoverpasswordform'>
  <dt {$recoverpasswordform_ldapserver_style}>{$recoverpasswordform_label_ldapserver|escape:"htmlall"}</dt>
  <dd {$recoverpasswordform_ldapserver_style}>
    <select name='LSsession_ldapserver' id='LSsession_ldapserver'>{html_options values=$recoverpasswordform_ldapservers_index output=$recoverpasswordform_ldapservers_name selected=$ldapServerId}</select>
  </dd>
  <dt>{$recoverpasswordform_label_user|escape:"htmlall"}</dt>
  <dd><input type='text' name='LSsession_user' /></dd>
  <dd><input type='submit' value='{$recoverpasswordform_label_submit|escape:"htmlall"}' /></dd>
</dl>
</form>
{/if}

<p id='recoverpassword_msg'>{$recoverpassword_msg|escape:"htmlall"}</p>
<span>{$lang_label|escape:"htmlall"} : <img id='LSlang' src='{img name=$LSlang}' alt='{$LSlang|escape:"htmlall"}' title='{$LSlang|escape:"htmlall"}'/></span>
<a href='index.php' id='recoverpassword_back'>{$recoverpasswordform_label_back|escape:"htmlall"}</a>
</div>
</body>
</html>
