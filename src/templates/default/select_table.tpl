<table class='LSobject-list' id='LSselect-object' caption='{$LSsearch->LSobject|escape:"htmlall"}'>
  <tr class='LSobject-list'>
    <th class='LSobject-list LSobject-select-check'></th>
    <th class='LSobject-list{if $LSsearch->sort} sortBy_displayName{/if}'>
      {if $LSsearch->sortBy == 'displayName'}
        <strong>{$LSsearch->label_objectName|escape:"htmlall"}</strong>
        <img src='{img name=$LSsearch->sortDirection}' class='LSobject-list-ordersense' alt='{$LSsearch->sortDirection}'/>
      {else}
        {$LSsearch->label_objectName|escape:"htmlall"}
      {/if}
    </th>
    {if $LSsearch->displaySubDn}
      <th class='LSobject-list LSobject-list-subdn{if $LSsearch->sort} sortBy_subDn{/if}'>
        {if $LSsearch->sort}
          {if $LSsearch->sortBy == 'subDn'}
            <strong>{$LSsearch->label_level|escape:"htmlall"}</strong>
            <img src='{img name=$LSsearch->sortDirection}' class='LSobject-list-ordersense' alt='{$LSsearch->sortDirection|escape:"htmlall"}'/>
          {else}
            {$LSsearch->label_level|escape:"htmlall"}
          {/if}
        {else}
          {$LSsearch->label_level|escape:"htmlall"}
        {/if}
      </th>
    {/if}
  </tr>
{foreach from=$page.list item=object}
    <tr class='{cycle values="LSobject-list,LSobject-list LSobject-list-bis"}'>
        <td class='LSobject-list LSobject-select-check'>
          <input type='{if $searchForm.multiple}checkbox{else}radio{/if}' name='LSobjects_selected[]'
          value='{$object->dn|escape:"htmlall"}' {if $object->selected}checked="true"{/if}
          {if !$object->selectable}disabled="disabled"{/if} class='LSobject-select' />
        </td>
        <td class='LSobject-list LSobject-select-names'>{$object->displayName|escape:"htmlall"}</td>
        {if $LSsearch->displaySubDn}
          <td class='LSobject-list LSobject-select-level'>{$object->subDn|escape:"htmlall"}</td>
        {/if}
    </tr>
{foreachelse}
    <tr class='LSobject-list'>
      <td colspan='3' class='LSobject-list-without-result'>{$LSsearch->label_no_result|escape:"htmlall"}</td>
    </tr>
{/foreach}
</table>

{include file='ls:pagination.tpl'}

<div id='LSdebug_txt_ajax' style='display: none'>{$LSdebug_txt}</div>
<div id='LSerror_txt_ajax' style='display: none'>{$LSerror_txt}</div>
