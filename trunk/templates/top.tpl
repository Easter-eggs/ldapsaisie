<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle}{/if}</title>
    <link rel="stylesheet" type="text/css" href="templates/css/base.css" title="Normal" />
    <link rel="stylesheet" type="text/css" href="templates/css/base_print.css" media='print' title="Normal" />
    {$LSsession_css}
    {$LSsession_js}
  </head>
<body>
<div id='LSjsConfig'>
{$LSjsConfig}
</div>
<div id='LSinfos'>{$LSinfos}</div>
<div id='LSerror'>
{$LSerrors}
</div>
<div id='LSdebug'>
  <span id='LSdebug_hidden'>X</span> 
  <div id='LSdebug_infos'>{if $LSdebug != ''}{$LSdebug}{/if}</div>
</div>

<div id='main'>
  <div id='left'>
    <a href='index.php'><img src='templates/images/logo.png' alt='Logo' id='logo'/></a>
    
    {if $LSsession_subDn!=""}
      <form action="index.php" method='post' id='LSsession_topDn_form'>
        <label>{$label_level}
          <a href="index.php?LSsession_refresh"><img src='templates/images/refresh.png' alt='{$_refresh}' title='{$_refresh}' /></a>
          <select name='LSsession_topDn' id='LSsession_topDn'>
            {html_options values=$LSsession_subDn_indexes output=$LSsession_subDn_names selected=$LSsession_subDn}
          </select>
        </label>
      </form>
    {/if}
    <ul class='menu'>
    {foreach from=$LSaccess item=label key=LSobject_type}
      <li class='menu'><a href='view.php?LSobject={$LSobject_type}' class='menu'>{$label}</a></li>
    {/foreach}
    </ul>
  </div>
  <div id='right'>
    
    

    <p id='status'>
    Connecté en tant que <span id='user_name'>{$LSsession_username}</span></b> <a href='index.php?LSsession_logout'><img src='templates/images/logout.png' alt='Logout' title='Logout' /></a>
    </p>
