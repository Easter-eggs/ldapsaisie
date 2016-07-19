<h1 id='LSrelation_title_{$item.id}' class='LSrelation'>
  {$item.label}
{if $item.actions!=''}
  {foreach from=$item.actions item=action}
    <a href='{$action.url}' class='btn btn-sm btn-default LSrelation_modify' id='{$item.id}'><img src='{img name=$action.action}' alt='{$action.label}' title='{$action.label}' /> {$action.label}</a>
  {/foreach}
{/if}
</h1>
<ul id='LSrelation_ul_{$item.id}' class='LSrelation'>
{foreach from=$item.objectList item=object}
  <li class='LSrelation'><a href='view.php?LSobject={$item.LSobject}&amp;dn={$object.dn|escape:'url'}' class='LSrelation{if $object.canEdit} LSrelation_editable{/if}' id='LSrelation_{$item.id}_{$object.dn}'>{$object.text}</a></li>
{foreachelse}
  <li class='LSrelation'>{$item.emptyText}</li>
{/foreach}
</ul>
