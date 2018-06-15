<h1 id='LSrelation_title_{$item.id|escape:"quotes"}' class='LSrelation'>{$item.label|escape:"htmlall"}</h1>
{if $item.actions!=''}
  <ul class='LSview-actions'>
  {foreach from=$item.actions item=action}
    <li class='LSview-actions'><a href='{$action.url}' class='LSview-actions LSrelation_modify' id='{$item.id|escape:"quotes"}'><img src='{img name=$action.action}' alt='{$action.label|escape:"htmlall"}' title='{$action.label|escape:"htmlall"}' /> {$action.label|escape:"htmlall"}</a></li>
  {/foreach}
  </ul>
{/if}
<ul id='LSrelation_ul_{$item.id|escape:"quotes"}' class='LSrelation'>
{foreach from=$item.objectList item=object}
  <li class='LSrelation'><a href='view.php?LSobject={$item.LSobject|escape:"url"}&amp;dn={$object.dn|escape:'url'}' class='LSrelation{if $object.canEdit} LSrelation_editable{/if}' id='LSrelation_{$item.id|escape:"quotes"}_{$object.dn|escape:"quotes"}'>{$object.text|escape:"htmlall"}</a></li>
{foreachelse}
  <li class='LSrelation'>{$item.emptyText|escape:"htmlall"}</li>
{/foreach}
</ul>
