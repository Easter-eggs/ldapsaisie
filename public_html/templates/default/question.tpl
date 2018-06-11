{include file='ls:top.tpl'}
    {if $pagetitle != ''}<h1>{$pagetitle|escape:"htmlall"}</h1>{/if}
    {if $LSview_actions != ''}
    <p class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <a href='{$item.url|escape:"quotes"}' class='LSview-actions'><img src='{img name=$item.action}' alt='{$item.label|escape:"htmlall"}' title='{$item.label|escape:"htmlall"}' /></a>
      {/foreach}
    </p>
    {/if}
    
    <p class='question'>{$question|escape:"htmlall"}</p>
    <a href='{$validation_url|escape:"quotes"}' class='question'>{$validation_label|escape:"htmlall"}</a>
{include file='ls:bottom.tpl'}
