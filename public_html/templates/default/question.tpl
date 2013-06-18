{include file='ls:top.tpl'}
    {if $pagetitle != ''}<h1>{$pagetitle}</h1>{/if}
    {if $LSview_actions != ''}
    <p class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <a href='{$item.url}' class='LSview-actions'><img src='{img name=$item.action}' alt='{$item.label}' title='{$item.label}' /></a>
      {/foreach}
    </p>
    {/if}
    
    <p class='question'>{$question}</p>
    <a href='{$validation_url}' class='question'>{$validation_label}</a>
{include file='ls:bottom.tpl'}
