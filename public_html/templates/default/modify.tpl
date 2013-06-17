{include file='ls:top.tpl'}
    {if $pagetitle != ''}<h1 id='LSform_title'>{$pagetitle}</h1>{/if}
    {if $LSview_actions != ''}
    <ul class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <li class='LSview-actions'><a href='{$item.url}' class='LSview-actions'><img src='{$LS_IMAGES_DIR}/{$item.action}.png' alt='{$item.label}' title='{$item.label}' /> {$item.label}</a></li>
      {/foreach}
    </ul>
    {/if}
    
    {include file='ls:LSform.tpl'}
    
{include file='ls:bottom.tpl'}
