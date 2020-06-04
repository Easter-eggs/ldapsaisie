<h1 id='LSrelation_title_{$item.id|escape:"quotes"}' class='LSrelation'>{$item.label|escape:"htmlall"}</h1>
{if $item.actions}
  {assign var=LSview_actions value=$item.actions}
  {include file='ls:LSview_actions.tpl'}
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
