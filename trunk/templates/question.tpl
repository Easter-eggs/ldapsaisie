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
    {if $LSview_actions != ''}
    <p class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <a href='{$item.url}' class='LSview-actions'><img src='templates/images/{$item.action}.png' alt='{$item.label}' title='{$item.label}' /></a>
      {/foreach}
    </p>
    {/if}
    
    <p class='question'>{$question}</p>
    {if $validation_txt!=''}<a href='{$validation_url}' class='question'>Valider</a>{/if}
  </div>
  <hr class='spacer' />
</div>
</body>
</html>
