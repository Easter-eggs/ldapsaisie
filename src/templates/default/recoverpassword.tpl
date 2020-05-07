{extends file='ls:base.tpl'}
{block name="css"}
<link rel="stylesheet" type="text/css" href="{css name='recoverpassword.css'}" media="screen" title="Normal" />
{include file='ls:LSsession_css.tpl'}
{/block}
{block name="body"}
<div class='recoverpasswordform'>
<img src='{img name='logo'}' alt='Logo' id='recoverpasswordform_logo' />
<div id='loading_zone'></div>
{if $recoverpassword_step == 'start'}
<form action='index?LSsession_recoverPassword' method='post'>
<dl class='recoverpasswordform'>
  <dt {if count($ldapservers) <= 1}style="display: none"{/if}>{tr msg="LDAP server"}</dt>
  <dd {if count($ldapservers) <= 1}style="display: none"{/if}>
    <select name='LSsession_ldapserver' id='LSsession_ldapserver'>{html_options options=$ldapservers selected=$ldapServerId}</select>
  </dd>
  <dt>{tr msg="Identifier"}</dt>
  <dd><input type='text' name='LSsession_user' /></dd>
  <dd><input type='submit' value='{tr msg="Validate"|escape:"quotes"}' /></dd>
</dl>
</form>
{/if}

<p id='recoverpassword_msg'>{$recoverpassword_msg|escape:"htmlall"}</p>
<span>{tr msg="Language"} : <img id='LSlang' src='{img name=$LSlang}' alt='{$LSlang|escape:"htmlall"}' title='{$LSlang|escape:"htmlall"}'/></span>
<a href='' id='recoverpassword_back'>{tr msg="Back"}</a>
</div>
{/block}
