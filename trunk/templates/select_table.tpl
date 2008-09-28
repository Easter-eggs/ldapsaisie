<table class='LSobject-list' id='LSselect-object' caption='{$LSobject_list_objecttype}'>
  <tr class='LSobject-list'>
    <th class='LSobject-list LSobject-select-check'></th>
    <th class='LSobject-list sortBy_displayValue'>{if $LSobject_list_orderby == 'displayValue'}<strong>{$LSobject_list_objectname}</strong><img src='templates/images/{$LSobject_list_ordersense}.png' class='LSobject-list-ordersense' alt='{$LSobject_list_ordersense}'/>{else}{$LSobject_list_objectname}{/if}</th>
    {if $LSobject_list_subDn}<th class='LSobject-list LSobject-list-subdn sortBy_subDn'>{if $LSobject_list_orderby == 'subDn'}<strong>{$label_level}</strong><img src='templates/images/{$LSobject_list_ordersense}.png' class='LSobject-list-ordersense' alt='{$LSobject_list_ordersense}'/>{else}{$label_level}{/if}</th>{/if}
  </tr>
{assign var='bis' value=false}
{foreach from=$LSobject_list item=object}
    <tr class='LSobject-list{if $bis} LSobject-list-bis{assign var='bis' value=false}{else}{assign var='bis' value=true}{/if}'>
        <td class='LSobject-list LSobject-select-check'><input type='{if $LSselect_multiple}checkbox{else}radio{/if}' name='LSobjects_selected[]' value='{$object.dn}' {if $object.select}checked{/if} class='LSobject-select' /></td>
        <td class='LSobject-list LSobject-select-names'>{$object.displayValue}</td>
        {if $LSobject_list_subDn}<td class='LSobject-list LSobject-select-level'>{$object.subDn}</td>{/if}
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
      <a href='select.php?LSobject={$LSobject_list_objecttype}&amp;multiple={$LSselect_multiple}&amp;page={$smarty.section.listpage.index}&amp;{$LSobject_list_filter}'  class='LSobject-list-page'>{$smarty.section.listpage.index+1}</a> 
    {/if}
  {/section}
  </p>
{/if}
<div id='LSdebug_txt'>{$LSdebug_txt}</div>
<div id='LSerror_txt'>{$LSerror_txt}</div>
