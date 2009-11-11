<h1 id='LSrelation_title_{$item.id}' class='LSrelation'>{$item.label}</h1>
{if $item.actions!=''}
  <ul class='LSview-actions'>
  {foreach from=$item.actions item=action}
    <li class='LSview-actions'><a href='{$action.url}' class='LSview-actions LSrelation_modify' id='{$item.id}'><img src='{$LS_IMAGES_DIR}/{$action.action}.png' alt='{$action.label}' title='{$action.label}' /> {$action.label}</a></li>
  {/foreach}
  </ul>
{/if}
<ul id='LSrelation_ul_{$item.id}' class='LSrelation'>
{foreach from=$item.objectList item=object}
  <li class='LSrelation'><a href='view.php?LSobject={$item.LSobject}&amp;dn={$object.dn}' class='LSrelation{if $object.canEdit} LSrelation_editable{/if}' id='{$object.dn}'>{$object.text}</a></li>
{foreachelse}
  <li class='LSrelation'>{$item.emptyText}</li>
{/foreach}
</ul>
