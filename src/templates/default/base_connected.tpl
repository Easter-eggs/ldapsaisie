{extends file='ls:base.tpl'}
{block name="body"}
<table id='main'>
  <tr>
    <td rowspan=2 id='left'>
      <button id="toggle-menu"><img src="{img name='toggle-menu'}" alt='{tr msg="Show/hide menu"}' title='{tr msg="Show/hide menu"}'/></button>
      <a href=''><img src='{img name='logo'}' alt='Logo' id='logo'/></a>

      {if isset($LSsession_subDn) && $LSsession_subDn!=""}
        <form action="" method='post' id='LSsession_topDn_form'>
          <label>{$label_level|escape:"htmlall"}
            <a href="?LSsession_refresh"><img src='{img name='refresh'}' alt='{$_refresh|escape:"htmlall"}' title='{$_refresh|escape:"htmlall"}' /></a>
            <select name='LSsession_topDn' id='LSsession_topDn'>
              {html_options values=$LSsession_subDn_indexes output=$LSsession_subDn_names selected=$LSsession_subDn}
            </select>
          </label>
        </form>
      {/if}

      {if $globalSearch && $LSaccess}
      <form action='search' method='post' class='LSview_search LSglobal_search'>
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
    <span>{tr msg="Language"} : <img id='LSlang' src='{img name=$LSlang}' alt='{$LSlang|escape:"htmlall"}' title='{$LSlang|escape:"htmlall"}'/></span>
    <form action='' methode='post' style='display: none' class='LSlang_hidden'>
      <select name='lang'>
      {foreach from=$LSlanguages item=lang}
        <option value='{$lang|escape:"htmlall"}'>{$lang|escape:"htmlall"}</option>
      {/foreach}
      </select>
      <input type='submit' value='->'/>
    </form>
    {if $displaySelfAccess}{tr msg="Connected as"|escape:"htmlall"} <span id='user_name'>{$LSsession_username|escape:"htmlall"}</span>{/if}
    <a href='?LSsession_refresh=1'><img src='{img name='refresh'}' alt="{tr msg="Refresh my access rights"}" title="{tr msg="Refresh my access rights"}" /></a>
    {if $displayLogoutBtn} <a href='?LSsession_logout'><img src='{img name='logout'}' alt='{tr msg="Logout"|escape:"htmlall"}' title='{tr msg="Logout"|escape:"htmlall"}' /></a>{/if}
    </td>
  </tr>
  <tr>
    <td id='right'>

{block name="content"}{/block}

    </td>
  </tr>
</table>
{/block}
