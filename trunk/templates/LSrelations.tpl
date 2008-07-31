<h1 id='LSrelation_title_{$item.id}'>{$item.label}</h1>
{if $item.actions!=''}
  <ul class='LSview-actions'>
  {foreach from=$item.actions item=action}
    <li class='LSview-actions'><a href='{$action.url}' class='LSview-actions LSrelation_modify' id='{$item.id}'><img src='templates/images/{$action.action}.png' alt='{$action.label}' title='{$action.label}' /> {$action.label}</a></li>
  {/foreach}
  </ul>
{/if}
<ul id='LSrelation_ul_{$item.id}' class='LSrelation'>
{foreach from=$item.objectList item=object}
    <li class='LSrelation'><a href='view.php?LSobject={$item.LSobject}&amp;dn={$object.dn}' class='LSrelation'><span id='{$object.dn}'>{$object.text}</span></a></li>
{/foreach}
</ul>
