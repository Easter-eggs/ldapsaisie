<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle|escape:"htmlall"}{/if}</title>
    <base href="{$public_root_url}/"/>
    <link rel="icon" href="image/favicon" />
    <link rel="stylesheet" type="text/css" href="{css name='login.css'}" media="screen" title="Normal" />
    {include file='ls:LSsession_css.tpl'}
  </head>
<body>

{include file='ls:LSdefault.tpl'}

<div class='loginform'>
<img src='{img name='logo'}' alt='Logo' id='loginform_logo' />
<div id='loading_zone'></div>
<form action='{if $request->current_url}{$request->current_url}{else}index{/if}' method='post'>
<dl class='loginform'>
  <dt {if count($ldapservers) <= 1}style="display: none"{/if}>{tr msg="LDAP server"}</dt>
  <dd {if count($ldapservers) <= 1}style="display: none"{/if}>
    <select name='LSsession_ldapserver' id='LSsession_ldapserver'>{html_options options=$ldapservers selected=$ldapServerId}</select>
  </dd>
  <dt class='loginform-level' id='LSsession_topDn_label' {if count($ldapservers) <= 1}style="display: none"{/if}>{tr msg="Level"}</dt>
  <dd class='loginform-level' {if count($ldapservers) <= 1}style="display: none"{/if}><select name='LSsession_topDn' id='LSsession_topDn'></select></dd>
  <dt>{tr msg="Identifier"}</dt>
  <dd><input type='text' name='LSauth_user' /></dd>
  <dt>{tr msg="Password"}</dt>
  <dd><input type='password' name='LSauth_pwd' /></dd>
  <dt class='LSlang_hidden'>{tr msg="Language"}</dt>
  <dd class='LSlang_hidden'>
  <select name='lang'>
  {foreach from=$LSlanguages item=lang}
    <option value='{$lang}'>{$lang|escape:"htmlall"}</option>
  {/foreach}
  </select>
  </dd>
  <dd><input type='submit' value='{tr msg="Connect"|escape:"quotes"}' /></dd>
</dl>
</form>
<span>{tr msg="Language"} : <img id='LSlang' src='{img name=$LSlang}' alt='{$LSlang|escape:"htmlall"}' title='{$LSlang|escape:"htmlall"}'/></span>
<a href='?LSsession_recoverPassword' class='LSsession_recoverPassword LSsession_recoverPassword_hidden'>{tr msg="Forgot your password ?"}</a>
</div>
{include file='ls:LSsession_js.tpl'}
</body>
</html>
