<h1 id='LSrelation_title_{$item.id|escape:"quotes"}' class='LSrelation'>{$item.label|escape:"htmlall"}</h1>
{if $item.actions!=''}
  <ul class='LSview-actions'>
  {foreach from=$item.actions item=action}
    <li class='LSview-actions'><a href='{$action.url}' class='LSview-actions{if $action.class} {$action.class}{/if}' id='{$item.id|escape:"quotes"}'><img src='{img name=$action.action}' alt='{$action.label|escape:"htmlall"}' title='{$action.label|escape:"htmlall"}' /> {$action.label|escape:"htmlall"}</a></li>
  {/foreach}
  </ul>
{/if}
<ul id='LSrelation_ul_{$item.id|escape:"quotes"}' class='LSrelation'>
{if isset($item['objectList']) && !empty($item.objectList)}
  {foreach from=$item.objectList item=object}
  <li class='LSrelation'><a href='object/{$item.LSobject|escape:"url"}/{$object.dn|escape:'url'}' class='LSrelation{if $object.canEdit} LSrelation_editable{/if}' id='LSrelation_{$item.id|escape:"quotes"}_{$object.dn|escape:"quotes"}'>{$object.text|escape:"htmlall"}</a></li>
  {/foreach}
{else}
  <li class='LSrelation'>{$item.emptyText|escape:"htmlall"}</li>
{/if}
</ul>
