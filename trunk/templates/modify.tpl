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
    {foreach from=$LSaccess item=item key=LSobject}
      <li class='menu'><a href='view.php?LSobject={$LSobject}' class='menu'>{$item.label}</a></li>
    {/foreach}
    </ul>
  </div>
  <div id='right'>
    <p id='status'>Connecté en tant que <span id='user_name'>{$LSsession_username}</span></b> <a href='index.php?LSsession_logout'><img src='templates/images/logout.png' alt='Logout' title='Logout' /></a></p>
    
    {if $pagetitle != ''}<h1>{$pagetitle}</h1>{/if}
    <p class='LSform-view-actions'><a href='view.php?LSobject={$LSform_object.type}&amp;dn={$LSform_object.dn}' class='LSform-view-actions'>Voir</a></p>
    
    <form action='{$LSform_action}' method='post' class='LSform'>
    {$LSform_header}
    <dl class='LSform'>
      {foreach from=$LSform_fields item=field}
      <dt class='LSform'>{$field.label}</dt>
      <dd class='LSform'>{$field.html}{if $field.add != ''} <span class='LSform-addfield'>+ Ajouter un champ</span>{/if}</dd>
      {if $field.errors != ''}
        {foreach from=$field.errors item=error}
        <dd class='LSform LSform-errors'>{$error}</dd>
        {/foreach}
      {/if}
      {/foreach}
      <dd class='LSform'><input type='submit' value='{$LSform_submittxt}' class='LSform' /></dd>
    </dl>
    </form>
  </div>
  <hr class='spacer' />
</div>
</body>
</html>
