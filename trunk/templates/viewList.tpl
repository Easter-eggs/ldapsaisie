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
    <table class='LSobject-list'>
      <tr class='LSobject-list'>
        <th class='LSobject-list'>{$LSobject_list_objectname}</th>
        <th class='LSobject-list'>{$_Actions}</th>
      </tr>
    {foreach from=$LSobject_list item=object}
        <tr class='LSobject-list'>
            <td class='LSobject-list LSobject-list-names'><a href='view.php?LSobject={$LSobject_list_objecttype}&amp;dn={$object.dn}'  class='LSobject-list'>{$object.displayValue}</a> </td>
            <td class='LSobject-list LSobject-list-actions'>{if $object.canEdit}<a href='modify.php?LSobject={$LSobject_list_objecttype}&amp;dn={$object.dn}'  class='LSobject-list-actions'><img src='templates/images/edit.png' alt='{$_Modifier}' title='{$_Modifier}'/></a>{/if}</td>
        </tr>
    {/foreach}
    </table>
    {if $LSobject_list_nbpage}
      <p class='LSobject-list-page'>
      {section name=listpage loop=$LSobject_list_nbpage step=1}
        {if $LSobject_list_currentpage == $smarty.section.listpage.index}
          <strong class='LSobject-list-page'>{$LSobject_list_currentpage}</strong> 
        {else}
          <a href='view.php?LSobject={$LSobject_list_objecttype}&amp;page={$smarty.section.listpage.index}'  class='LSobject-list-page'>{$smarty.section.listpage.index}</a> 
        {/if}
      {/section}
      </p>
    {/if}
  </div>
  <hr class='spacer' />
</div>
</body>
</html>
