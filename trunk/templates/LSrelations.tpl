<h1 id='LSrelation_title_{$item.id}'>{$item.label}</h1>
{if $item.actions!=''}
  <p class='LSview-actions'>
  {foreach from=$item.actions item=action}
    <a href='{$action.url}' class='LSview-actions LSrelation_modify' id='{$item.id}'><img src='templates/images/{$action.action}.png' alt='{$action.label}' title='{$action.label}' /> {$action.label}</a>
  {/foreach}
  </p>
{/if}
<ul id='LSrelation_ul_{$item.id}' class='LSrelation'>
{foreach from=$item.objectList item=object}
    <li class='LSrelation'>{$object}</li>
{/foreach}
</ul>
