<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle}{/if}</title>
    <link rel="icon" type="image/png" href="images/default/favicon.png" />
    <link rel="stylesheet" type="text/css" href="{css name='login.css'}" media="screen" title="Normal" />
    {$LSsession_css}
    {$LSsession_js}
  </head>
<body>

{include file='ls:LSdefault.tpl'}

<div class='loginform'>
<img src='{img name='logo'}' alt='Logo' id='loginform_logo' />
<div id='loading_zone'></div>
<form action='{$loginform_action}' method='post'>
<dl class='loginform'>
  <dt {$loginform_ldapserver_style}>{$loginform_label_ldapserver}</dt>
  <dd {$loginform_ldapserver_style}>
    <select name='LSsession_ldapserver' id='LSsession_ldapserver'>{html_options values=$loginform_ldapservers_index output=$loginform_ldapservers_name selected=$ldapServerId}</select>
  </dd>
  <dt class='loginform-level' id='LSsession_topDn_label' {$loginform_ldapserver_style}>{$loginform_label_level}</dt>
  <dd class='loginform-level' {$loginform_ldapserver_style}><select name='LSsession_topDn' id='LSsession_topDn'>{html_options values=$loginform_topdn_index output=$loginform_topdn_name selected=$topDn}</select></dd>
  <dt>{$loginform_label_user}</dt>
  <dd><input type='text' name='LSauth_user' /></dd>
  <dt>{$loginform_label_pwd}</dt>
  <dd><input type='password' name='LSauth_pwd' /></dd>
  <dt class='LSlang_hidden'>{$lang_label}</dt>
  <dd class='LSlang_hidden'>
  <select name='lang'>
  {foreach from=$LSlanguages item=lang}
    <option value='{$lang}'>{$lang}</option>
  {/foreach}
  </select>
  </dd>
  <dd><input type='submit' value='{$loginform_label_submit}' /></dd>
</dl>
</form>
<span>{$lang_label} : <img id='LSlang' src='{img name=$LSlang}' alt='{$LSlang}' title='{$LSlang}'/></span>
<a href='index.php?LSsession_recoverPassword' class='LSsession_recoverPassword LSsession_recoverPassword_hidden'>{$loginform_label_recoverPassword}</a>
</div>
</body>
</html>
