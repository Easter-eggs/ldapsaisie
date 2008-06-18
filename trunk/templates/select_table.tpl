<table class='LSobject-list' id='LSselect-object' caption='{$LSobject_list_objecttype}'>
  <tr class='LSobject-list'>
    <th class='LSobject-list LSobject-select-check'></th>
    <th class='LSobject-list'>{$LSobject_list_objectname}</th>
    {if $label_level}<th class='LSobject-list'>{$label_level}</th>{/if}
  </tr>
{foreach from=$LSobject_list item=object}
    <tr class='LSobject-list{if $object.tr=='bis'} LSobject-list-bis{/if}'>
        <td class='LSobject-list LSobject-select-check'><input type='checkbox' name='LSobjects_selected[]' value='{$object.dn}' {if $object.select}checked{/if} class='LSobject-select' /></td>
        <td class='LSobject-list LSobject-select-names'>{$object.displayValue}</td>
        {if $label_level}<td class='LSobject-list LSobject-select-level'>{$object.subDn}</td>{/if}
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
      <a href='select.php?LSobject={$LSobject_list_objecttype}&amp;page={$smarty.section.listpage.index}&amp;{$LSobject_list_filter}'  class='LSobject-list-page'>{$smarty.section.listpage.index+1}</a> 
    {/if}
  {/section}
  </p>
{/if}
<script type='text/javascript'>
debug_txt = {$debug_txt};
error_txt = {$error_txt};
</script>
