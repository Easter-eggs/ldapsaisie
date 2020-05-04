<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset={$LSencoding}">
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle|escape:"htmlall"}{/if}</title>
    <base href="{$public_root_url}/"/>
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

      {if isset($LSsession_subDn) && $LSsession_subDn!=""}
        <form action="index.php" method='post' id='LSsession_topDn_form'>
          <label>{$label_level|escape:"htmlall"}
            <a href="index.php?LSsession_refresh"><img src='{img name='refresh'}' alt='{$_refresh|escape:"htmlall"}' title='{$_refresh|escape:"htmlall"}' /></a>
            <select name='LSsession_topDn' id='LSsession_topDn'>
              {html_options values=$LSsession_subDn_indexes output=$LSsession_subDn_names selected=$LSsession_subDn}
            </select>
          </label>
        </form>
      {/if}

      {if $globalSearch && $LSaccess}
      <form action='global_search.php' method='post' class='LSview_search LSglobal_search'>
        <input type='hidden' name='LSsearch_submit' value='1'/>
        <input type='text' name='pattern' class='LSview_search LSglobal_search' placeholder='{tr msg='Global search'}' required="required"/>
        <input type='image' src='{img name='find'}' alt='{tr msg='Global search'}' title='{tr msg='Global search'}' />
      </form>
      {/if}

      <ul class='menu'>
      {foreach from=$LSaccess item=label key=LSobject_type}
        <li class='menu'><a href='object/{$LSobject_type|escape:"url"}' class='menu'>{tr msg=$label}</a></li>
      {/foreach}
      {foreach from=$LSaddonsViewsAccess item=access}
        {if $access.showInMenu}
        <li class='menu'><a href='addon/{$access.LSaddon|escape:"url"}/{$access.id|escape:"url"}' class='menu'>{tr msg=$access.label}</a></li>
        {/if}
      {/foreach}
      </ul>
    </td>
    <td id='status'>
    <span>{$lang_label|escape:"htmlall"} : <img id='LSlang' src='{img name=$LSlang}' alt='{$LSlang|escape:"htmlall"}' title='{$LSlang|escape:"htmlall"}'/></span>
    <form action='' methode='post' style='display: none' class='LSlang_hidden'>
      <select name='lang'>
      {foreach from=$LSlanguages item=lang}
        <option value='{$lang|escape:"htmlall"}'>{$lang|escape:"htmlall"}</option>
      {/foreach}
      </select>
      <input type='submit' value='->'/>
    </form>
    {if $displaySelfAccess}{$connected_as|escape:"htmlall"} <span id='user_name'>{$LSsession_username|escape:"htmlall"}</span>{/if}
    <a href='index.php?LSsession_refresh=1'><img src='{img name='refresh'}' alt="{tr msg="Refresh my access rights"}" title="{tr msg="Refresh my access rights"}" /></a>
    {if $displayLogoutBtn} <a href='index.php?LSsession_logout'><img src='{img name='logout'}' alt='Logout' title='Logout' /></a>{/if}
    </td>
  </tr>
  <tr>
    <td id='right'>
