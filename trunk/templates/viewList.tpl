{include file='top.tpl'}
      <form action='{$LSview_search.action}' method='post' class='LSview_search'>
        {foreach from=$LSview_search_hidden_fields item=value key=name}
          <input type='hidden' name='{$name}' value='{$value}' />
        {/foreach}
        <input type='text' name='LSview_pattern' class='LSview_search' value="{$LSview_search_pattern}"/>
        <input type='submit' value='{$LSview_search.submit}' name='LSview_search_submit' class='LSview_search' />
        <p id='LSview_search_param'>
          <label class='LSview_search'>{$LSview_search_approx_label} : <input type='checkbox' name='LSview_approx' class='LSview_search' {if $LSview_search_approx!=''}checked{/if} /></label>
          <label class='LSview_search'>{$LSview_search_recur_label} : <input type='checkbox' name='LSview_recur' class='LSview_search' {if $LSview_search_recur!=''}checked{/if}/></label>
        </p>
      </form>
    <h1>
      {$pagetitle}
    </h1>
    
    {if $LSview_actions != ''}
    <p class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <a href='{$item.url}' class='LSview-actions'><img src='templates/images/{$item.action}.png' alt='{$item.label}' title='{$item.label}' /> {$item.label}</a>
      {/foreach}
    </p>
    {/if}

      <table class='LSobject-list'>
        <tr class='LSobject-list'>
          <th class='LSobject-list'><a href='view.php?LSobject={$LSobject_list_objecttype}&amp;orderby=displayValue'>{if $LSobject_list_orderby == 'displayValue'}<strong>{$LSobject_list_objectname}</strong><img src='templates/images/{$LSobject_list_ordersense}.png' class='LSobject-list-ordersense' alt='{$LSobject_list_ordersense}'/>{else}{$LSobject_list_objectname}{/if}</a></th>
          {if $LSobject_list_subDn}<th class='LSobject-list LSobject-list-subdn'><a href='view.php?LSobject={$LSobject_list_objecttype}&amp;orderby=subDn'>{if $LSobject_list_orderby == 'subDn'}<strong>{$label_level}</strong><img src='templates/images/{$LSobject_list_ordersense}.png' class='LSobject-list-ordersense' alt='{$LSobject_list_ordersense}'/>{else}{$label_level}{/if}</a></th>{/if}
          <th class='LSobject-list'>{$_Actions}</th>
        </tr>
        {assign var='bis' value=false}
        {foreach from=$LSobject_list item=object}
        <tr class='LSobject-list{if $bis} LSobject-list-bis{assign var='bis' value=false}{else}{assign var='bis' value=true}{/if}'>
            <td class='LSobject-list LSobject-list-names'><a href='view.php?LSobject={$LSobject_list_objecttype}&amp;dn={$object.dn}'  class='LSobject-list'>{$object.displayValue}</a> </td>
            {if $LSobject_list_subDn}<td class='LSobject-list'>{$object.subDn}</td>{/if}
            <td class='LSobject-list LSobject-list-actions'>
            {if $object.actions!=''}
            {foreach from=$object.actions item=item}
              <a href='{$item.url}'  class='LSobject-list-actions'><img src='templates/images/{$item.action}.png' alt='{$item.label}' title='{$item.label}'/></a>
            {/foreach}
            {/if}
            </td>
        </tr>
        {foreachelse}
          <tr class='LSobject-list'>
            <td colspan='3' class='LSobject-list-without-result'>{$LSobject_list_without_result_label}</td>
          </tr>  
        {/foreach}
    </table>
    {if $LSobject_list_nbpage}
      <p class='LSobject-list-page'>
      {section name=listpage loop=$LSobject_list_nbpage step=1}
        {if $LSobject_list_currentpage == $smarty.section.listpage.index}
          <strong class='LSobject-list-page'>{$LSobject_list_currentpage+1}</strong> 
        {else}
          <a href='view.php?LSobject={$LSobject_list_objecttype}&amp;page={$smarty.section.listpage.index}&amp;{$LSobject_list_filter}'  class='LSobject-list-page'>{$smarty.section.listpage.index+1}</a> 
        {/if}
      {/section}
      </p>
    {/if}
{include file='bottom.tpl'}
