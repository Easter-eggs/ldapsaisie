<h2>
  {$pagetitle}
</h2>

<table class='LSobject-list'>
    <tr class='LSobject-list'>
      <th class='LSobject-list'>
        {if $LSsearch->sort}
        <a href='object/{$LSsearch->LSobject}?sortBy=displayName&amp;nocache={$smarty.now}'>
          {if $LSsearch->sortBy == 'displayName'}
            <strong>{$LSsearch->label_objectName|escape:'htmlall'}</strong>
            <img src='{img name=$LSsearch->sortDirection}' class='LSobject-list-ordersense' alt='{$LSsearch->sortDirection}'/>
          {else}
            {$LSsearch->label_objectName|escape:'htmlall'}
          {/if}
        </a>
        {else}
          {$LSsearch->label_objectName|escape:'htmlall'}
        {/if}
      </th>
      {if $LSsearch->displaySubDn}
        <th class='LSobject-list LSobject-list-subdn'>
        {if $LSsearch->sort}
          <a href='object/{$LSsearch->LSobject}?sortBy=subDn&amp;nocache={$smarty.now}'>
          {if $LSsearch->sortBy == 'subDn'}
            <strong>{$LSsearch->label_level|escape:'htmlall'}</strong>
            <img src='{img name=$LSsearch->sortDirection}' class='LSobject-list-ordersense' alt='{$LSsearch->sortDirection}'/>
          {else}
            {$LSsearch->label_level|escape:'htmlall'}
          {/if}
          </a>
        {else}
          {$LSsearch->label_level|escape:'htmlall'}
        {/if}
        </th>
      {/if}
      <th class='LSobject-list'>{$LSsearch->label_actions}</th>
    </tr>
    {foreach from=$page.list item=object}
    <tr class='{cycle values="LSobject-list,LSobject-list LSobject-list-bis"}'>
        <td class='LSobject-list LSobject-list-names'><a href='object/{$LSsearch->LSobject|escape:'url'}/{$object->dn|escape:'url'}'  class='LSobject-list'>{$object->displayName|escape:'htmlall'}</a> </td>
        {if $LSsearch->displaySubDn}<td class='LSobject-list'>{$object->subDn|escape:'htmlall'}</td>{/if}
        <td class='LSobject-list LSobject-list-actions'>
        {foreach from=$object->actions item=item}
          <a href='{$item.url|escape:'quotes'}'  class='LSobject-list-actions'><img src='{img name=$item.action|escape:'url'}' alt='{$item.label|escape:'quotes'}' title='{$item.label|escape:'quotes'}'/></a>
        {/foreach}
        </td>
    </tr>
    {foreachelse}
      <tr class='LSobject-list'>
        <td colspan='3' class='LSobject-list-without-result'>{$LSsearch->label_no_result|escape:'htmlall'}</td>
      </tr>
    {/foreach}
</table>
<span id='LSobject_list_nbresult'>{$LSsearch->label_total|escape:'htmlall'}</span>
{assign var=pagination_url value="object/{$LSsearch->LSobject|escape:"url"}"}
{include file='ls:pagination.tpl'}
