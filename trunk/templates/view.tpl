<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle}{/if}</title>
    <link rel="stylesheet" type="text/css" href="templates/css/base.css" media="screen" title="Normal" />
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

<div id='main'>
  <div id='left'>
    <img src='templates/images/logo.png' alt='Logo' id='logo'/>
    <ul class='menu'>
    {foreach from=$LSaccess item=item key=LSobject_type}
      <li class='menu'><a href='view.php?LSobject={$LSobject_type}' class='menu'>{$item.label}</a></li>
    {/foreach}
    </ul>
  </div>
  <div id='right'>
    <p id='status'>Connecté en tant que <span id='user_name'>{$LSsession_username}</span></b> <a href='index.php?LSsession_logout'><img src='templates/images/logout.png' alt='Logout' title='Logout' /></a></p>
    {if $pagetitle != ''}<h1>{$pagetitle}</h1>{/if}
    {if $LSform_canEdit == 'true'}<p class='LSform-view-actions'><a href='modify.php?LSobject={$LSform_object.type}&amp;dn={$LSform_object.dn}' class='LSform-view-actions'>Modifier</a></p>{/if}
    <dl class='LSform'>
      {foreach from=$LSform_fields item=field}
      <dt class='LSform'>{$field.label}</dt>
      <dd class='LSform'>{$field.html}</dd>
      {/foreach}
    </dl>
  </div>
  <hr class='spacer' />
</div>
</body>
</html>
