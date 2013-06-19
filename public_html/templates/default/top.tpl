<?xml version="1.0"?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset={$LSencoding}">
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle}{/if}</title>
    <link rel="icon" type="image/png" href="images/default/favicon.png" />
    <link rel="stylesheet" type="text/css" href="{css name='base.css'}" title="Normal" />
    <link rel="stylesheet" type="text/css" href="{css name='base_print.css'}" media='print' title="Normal" />
    {$LSsession_css}
  </head>
<body>

{include file='ls:LSdefault.tpl'}

<table id='main'>
  <tr>
    <td rowspan=2 id='left'>
      <a href='index.php'><img src='{img name='logo'}' alt='Logo' id='logo'/></a>
      
      {if $LSsession_subDn!=""}
        <form action="index.php" method='post' id='LSsession_topDn_form'>
          <label>{$label_level}
            <a href="index.php?LSsession_refresh"><img src='{img name='refresh'}' alt='{$_refresh}' title='{$_refresh}' /></a>
            <select name='LSsession_topDn' id='LSsession_topDn'>
              {html_options values=$LSsession_subDn_indexes output=$LSsession_subDn_names selected=$LSsession_subDn}
            </select>
          </label>
        </form>
      {/if}
      <ul class='menu'>
      {foreach from=$LSaccess item=label key=LSobject_type}
        <li class='menu'><a href='view.php?LSobject={$LSobject_type}' class='menu'>{tr msg=$label}</a></li>
      {/foreach}
      </ul>
    </td>
    <td id='status'>
    <span>{$lang_label} : <img id='LSlang' src='{img name=$LSlang}' alt='{$LSlang}' title='{$LSlang}'/></span>
    <form action='' methode='post' style='display: none' class='LSlang_hidden'>
      <select name='lang'>
      {foreach from=$LSlanguages item=lang}
        <option value='{$lang}'>{$lang}</option>
      {/foreach}
      </select>
      <input type='submit' value='->'/>
    </form>
    {if $displaySelfAccess}{$connected_as} <span id='user_name'>{$LSsession_username}</span>{/if}{if $displayLogoutBtn} <a href='index.php?LSsession_logout'><img src='{img name='logout'}' alt='Logout' title='Logout' /></a>{/if}
    </td>
  </tr>
  <tr>
    <td id='right'>
    
    

    
