{include file='ls:top.tpl'}
<form action='{$searchForm.action}' method='post' class='LSview_search' id='LSsearch_form'>

<div class='LSview_search'>
  {foreach from=$searchForm.hiddenFields item=value key=name}
    <input type='hidden' name='{$name}' value='{$value}' />
  {/foreach}
  
  <input type='text' name='pattern' class='LSview_search' value="{$searchForm.values.pattern}"/>
  <input type='submit' value='{$searchForm.labels.submit}' name='{$searchForm.names.submit}' class='LSview_search' />
  <p id='LSview_search_param'>
    <label class='LSview_search'>{$searchForm.labels.approx} : <input type='checkbox' name='approx' class='LSview_search' {if $searchForm.values.approx!=''}checked="true"{/if} /></label>
    {if $searchForm.recursive}<label class='LSview_search'>{$searchForm.labels.recursive} : <input type='checkbox' name='recursive' class='LSview_search' {if $searchForm.values.recursive!=''}checked="true"{/if}/></label>{/if}
  </p>
</div>

<h1>
  {$pagetitle}
</h1>

{if $LSview_actions != ''}
<ul class='LSview-actions'>
  {foreach from=$LSview_actions item=item}
    {if is_array($item)}
      <li class='LSview-actions'><a href='{$item.url}' class='LSview-actions'><img src='{img name=$item.action}' alt='{tr msg=$label}' title='{tr msg=$label}' /> {tr msg=$item.label}</a></li>
    {/if}
  {/foreach}
</ul>
{/if}

{if count($LSsearch->predefinedFilters) != 0}
  <select id='LSview_search_predefinedFilter' name='predefinedFilter'>
    <option value=''>--</option>
    {html_options options=$LSsearch->predefinedFilters selected=$searchForm.predefinedFilter}
  </select>
{/if}


</form>

<table class='LSobject-list'>
    <tr class='LSobject-list'>
      <th class='LSobject-list'>
        {if $LSsearch->sort}
        <a href='view.php?LSobject={$LSsearch->LSobject}&amp;sortBy=displayName&amp;nocache={$smarty.now}'>
          {if $LSsearch->sortBy == 'displayName'}
            <strong>{$LSsearch->label_objectName}</strong>
            <img src='{img name=$LSsearch->sortDirection}' class='LSobject-list-ordersense' alt='{$LSsearch->sortDirection}'/>
          {else}
            {$LSsearch->label_objectName}
          {/if}
        </a>
        {else}
          {$LSsearch->label_objectName}
        {/if}
      </th>
      {if $LSsearch->displaySubDn}
        <th class='LSobject-list LSobject-list-subdn'>
        {if $LSsearch->sort}
          <a href='view.php?LSobject={$LSsearch->LSobject}&amp;sortBy=subDn&amp;nocache={$smarty.now}'>
          {if $LSsearch->sortBy == 'subDn'}
            <strong>{$LSsearch->label_level}</strong>
            <img src='{img name=$LSsearch->sortDirection}' class='LSobject-list-ordersense' alt='{$LSsearch->sortDirection}'/>
          {else}
            {$LSsearch->label_level}
          {/if}
          </a>
        {else}
          {$LSsearch->label_level}
        {/if}
        </th>
      {/if}
      {if $LSsearch->extraDisplayedColumns}
        {foreach from=$LSsearch->extraDisplayedColumns item=conf key=cid}
        <th class='LSobject-list'>
        {if $LSsearch->sort}
          <a href='view.php?LSobject={$LSsearch->LSobject}&amp;sortBy={$cid}&amp;nocache={$smarty.now}'>
          {if $LSsearch->sortBy == $cid}
            <strong>{tr msg=$conf.label}</strong>
            <img src='{img name=$LSsearch->sortDirection}' class='LSobject-list-ordersense' alt='{$LSsearch->sortDirection}'/>
          {else}
            {tr msg=$conf.label}
          {/if}
          </a>
        {else}
          {tr msg=$conf.label}
        {/if}
        </th>
        {/foreach}
      {/if}
      <th class='LSobject-list'>{$LSsearch->label_actions}</th>
    </tr>
    {foreach from=$page.list item=object}
    <tr class='{cycle values="LSobject-list,LSobject-list LSobject-list-bis"}'>
        <td class='LSobject-list LSobject-list-names'><a href='view.php?LSobject={$LSsearch->LSobject}&amp;dn={$object->dn|escape:'url'}'  class='LSobject-list'>{$object->displayName}</a> </td>
        {if $LSsearch->displaySubDn}<td class='LSobject-list'>{$object->subDn}</td>{/if}
        {if $LSsearch->extraDisplayedColumns}
          {foreach from=$LSsearch->extraDisplayedColumns item=conf key=cid}
          <td class='LSobject-list'>{$object->$cid}</th>
          {/foreach}
        {/if}
        <td class='LSobject-list LSobject-list-actions'>
        {foreach from=$object->actions item=item}
          <a href='{$item.url}'  class='LSobject-list-actions'><img src='{img name=$item.action}' alt='{$item.label}' title='{$item.label}'/></a>
        {/foreach}
        </td>
    </tr>
    {foreachelse}
      <tr class='LSobject-list'>
        <td colspan='{if $LSsearch->extraDisplayedColumns}{count($LSsearch->extraDisplayedColumns)+3}{else}3{/if}' class='LSobject-list-without-result'>
          {$LSsearch->label_no_result}
        </td>
      </tr>   
    {/foreach}
</table>
<span id='LSobject_list_nbresult'>{$LSsearch->label_total}</span>
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
    <a href='view.php?LSobject={$LSsearch->LSobject}&amp;page=0' class='LSobject-list-page'><</a> 
    {foreach from=0|range:10 item=i}
      {if $page.nb==$start+$i}
        <strong class='LSobject-list-page'>{$page.nb+1}</strong> 
      {else}
        <a href='view.php?LSobject={$LSsearch->LSobject}&amp;page={$i+$start}'  class='LSobject-list-page'>{$i+$start+1}</a> 
      {/if}
    {/foreach}
    <a href='view.php?LSobject={$LSsearch->LSobject}&amp;page={$page.nbPages-1}' class='LSobject-list-page'>></a> 
  {else}
    {section name=listpage loop=$page.nbPages step=1}
      {if $page.nb == $smarty.section.listpage.index}
        <strong class='LSobject-list-page'>{$page.nb+1}</strong> 
      {else}
        <a href='view.php?LSobject={$LSsearch->LSobject}&amp;page={$smarty.section.listpage.index}'  class='LSobject-list-page'>{$smarty.section.listpage.index+1}</a> 
      {/if}
    {/section}
  {/if}
  </p>
{/if}
{include file='ls:bottom.tpl'}
