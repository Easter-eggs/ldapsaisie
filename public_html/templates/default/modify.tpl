{include file='ls:top.tpl'}
    {if $pagetitle != ''}<h1 id='LSform_title'>{$pagetitle|escape:"htmlall"}</h1>{/if}
    {if $LSview_actions != ''}
    <ul class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <li class='LSview-actions'><a href='{$item.url}' class='LSview-actions'><img src='{img name=$item.action}' alt='{$item.label|escape:"htmlall"}' title='{$item.label|escape:"htmlall"}' /> {$item.label|escape:"htmlall"}</a></li>
      {/foreach}
    </ul>
    {/if}
    
    {include file='ls:LSform.tpl'}
    
{include file='ls:bottom.tpl'}
