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
          <th class='LSobject-list'>{$LSobject_list_objectname}</th>
          {if $label_level}<th class='LSobject-list'>{$label_level}</th>{/if}
          <th class='LSobject-list'>{$_Actions}</th>
        </tr>
        {foreach from=$LSobject_list item=object}
        <tr class='LSobject-list{if $object.tr=='bis'} LSobject-list-bis{/if}'>
            <td class='LSobject-list LSobject-list-names'><a href='view.php?LSobject={$LSobject_list_objecttype}&amp;dn={$object.dn}'  class='LSobject-list'>{$object.displayValue}</a> </td>
            {if $label_level}<td class='LSobject-list LSobject-list-subdn'>{$object.subDn}</td>{/if}
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
