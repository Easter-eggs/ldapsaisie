<table class='LSobject-list' id='LSselect-object' caption='{$LSsearch->LSobject}'>
  <tr class='LSobject-list'>
    <th class='LSobject-list LSobject-select-check'></th>
    <th class='LSobject-list{if $LSsearch->sort} sortBy_displayName{/if}'>
      {if $LSsearch->sortBy == 'displayName'}
        <strong>{$LSsearch->label_objectName}</strong>
        <img src='{$LS_IMAGES_DIR}/{$LSsearch->sortDirection}.png' class='LSobject-list-ordersense' alt='{$LSsearch->sortDirection}'/>
      {else}
        {$LSsearch->label_objectName}
      {/if}
    </th>
    {if $LSsearch->displaySubDn}
      <th class='LSobject-list LSobject-list-subdn{if $LSsearch->sort} sortBy_subDn{/if}'>
        {if $LSsearch->sort}
          {if $LSsearch->sortBy == 'subDn'}
            <strong>{$LSsearch->label_level}</strong>
            <img src='{$LS_IMAGES_DIR}/{$LSsearch->sortDirection}.png' class='LSobject-list-ordersense' alt='{$LSsearch->sortDirection}'/>
          {else}
            {$LSsearch->label_level}
          {/if}
        {else}
          {$LSsearch->label_level}
        {/if}
      </th>
    {/if}
  </tr>
{foreach from=$page.list item=object}
    <tr class='{cycle values="LSobject-list,LSobject-list LSobject-list-bis"}'>
        <td class='LSobject-list LSobject-select-check'><input type='{if $searchForm.multiple}checkbox{else}radio{/if}' name='LSobjects_selected[]' value='{$object->dn}' {if $object->LSselect}checked="true"{/if}{if $searchForm.selectablly}{if !$object->selectablly} disabled="disabled"{/if}{/if} class='LSobject-select' /></td>
        <td class='LSobject-list LSobject-select-names'>{$object->displayName}</td>
        {if $LSsearch->displaySubDn}
          <td class='LSobject-list LSobject-select-level'>{$object->subDn}</td>
        {/if}
    </tr>
{foreachelse}
    <tr class='LSobject-list'>
      <td colspan='3' class='LSobject-list-without-result'>{$LSsearch->label_no_result}</td>
    </tr> 
{/foreach}
</table>


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
    <a href='select.php?LSobject={$LSsearch->LSobject}&amp;page=0&amp;multiple={$searchForm.multiple}' class='LSobject-list-page'><</a> 
    {foreach from=0|range:10 item=i}
      {if $page.nb==$start+$i}
        <strong class='LSobject-list-page'>{$page.nb+1}</strong> 
      {else}
        <a href='select.php?LSobject={$LSsearch->LSobject}&amp;page={$i+$start}&amp;multiple={$searchForm.multiple}'  class='LSobject-list-page'>{$i+$start+1}</a> 
      {/if}
    {/foreach}
    <a href='select.php?LSobject={$LSsearch->LSobject}&amp;page={$page.nbPages-1}&amp;multiple={$searchForm.multiple}' class='LSobject-list-page'>></a> 
  {else}
    {section name=listpage loop=$page.nbPages step=1}
      {if $page.nb == $smarty.section.listpage.index}
        <strong class='LSobject-list-page'>{$page.nb+1}</strong> 
      {else}
        <a href='select.php?LSobject={$LSsearch->LSobject}&amp;page={$smarty.section.listpage.index}&amp;multiple={$searchForm.multiple}'  class='LSobject-list-page'>{$smarty.section.listpage.index+1}</a> 
      {/if}
    {/section}
  {/if}
  </p>
{/if}

<div id='LSdebug_txt_ajax' style='display: none'>{$LSdebug_txt}</div>
<div id='LSerror_txt_ajax' style='display: none'>{$LSerror_txt}</div>
