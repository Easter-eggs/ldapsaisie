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
{if $page.nbPages > 1}
  <p class='LSobject-list-page'>

  {if $page.nbPages > 10}
    {if $page.nb > 5}
      {if $page.nb > $page.nbPages-6}
        {assign var=start value=$page.nbPages-12}
       {else}
        {assign var=start value=$page.nb-6}
      {/if}
    {else}
      {assign var=start value=0}
    {/if}
    <a href='object/{$LSsearch->LSobject|escape:'url'}?page=0' class='LSobject-list-page'>&lt;</a>
    {foreach from=0|range:10 item=i}
      {if $page.nb==$start+$i}
        <strong class='LSobject-list-page'>{$page.nb+1}</strong>
      {else}
        <a href='object/{$LSsearch->LSobject|escape:'url'}?page={$i+$start}'  class='LSobject-list-page'>{$i+$start+1}</a>
      {/if}
    {/foreach}
    <a href='object/{$LSsearch->LSobject|escape:'url'}?page={$page.nbPages-1}' class='LSobject-list-page'>&gt;</a>
  {else}
    {section name=listpage loop=$page.nbPages step=1}
      {if $page.nb == $smarty.section.listpage.index}
        <strong class='LSobject-list-page'>{$page.nb+1}</strong>
      {else}
        <a href='object/{$LSsearch->LSobject|escape:'url'}?page={$smarty.section.listpage.index}'  class='LSobject-list-page'>{$smarty.section.listpage.index+1}</a>
      {/if}
    {/section}
  {/if}
  </p>
{/if}
