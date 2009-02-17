<?xml version="1.0"?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle}{/if}</title>
    <link rel="icon" type="image/png" href="images/default/favicon.png" />
    <link rel="stylesheet" type="text/css" href="{$LS_CSS_DIR}/base.css" title="Normal" />
    <link rel="stylesheet" type="text/css" href="{$LS_CSS_DIR}/base_print.css" media='print' title="Normal" />
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


<table id='main'>
  <tr>
    <td rowspan=2 id='left'>
      <a href='index.php'><img src='{$LS_IMAGES_DIR}/logo.png' alt='Logo' id='logo'/></a>
      
      {if $LSsession_subDn!=""}
        <form action="index.php" method='post' id='LSsession_topDn_form'>
          <label>{$label_level}
            <a href="index.php?LSsession_refresh"><img src='{$LS_IMAGES_DIR}/refresh.png' alt='{$_refresh}' title='{$_refresh}' /></a>
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
    </td>
    <td id='status'>
    {$connected_as} <span id='user_name'>{$LSsession_username}</span> <a href='index.php?LSsession_logout'><img src='{$LS_IMAGES_DIR}/logout.png' alt='Logout' title='Logout' /></a>
    </td>
  </tr>
  <tr>
    <td id='right'>
    
    

    
