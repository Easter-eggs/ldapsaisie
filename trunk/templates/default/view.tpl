{include file='top.tpl'}
    {if $pagetitle != ''}<h1 id='LSview_title'>{$pagetitle}</h1>{/if}
    {if $LSview_actions != ''}
    <ul class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <li class='LSview-actions'><a href='{$item.url}' class='LSview-actions' ><img src='{$LS_IMAGES_DIR}/{$item.action}.png' alt='{$item.label}' title='{$item.label}' /> {$item.label}</a></li>
      {/foreach}
    </ul>
    {/if}
    
    {include file='LSform_view.tpl'}
    
    {if $LSrelations!=''}
      {foreach from=$LSrelations item=item}
        {include file='LSrelations.tpl'}
      {/foreach}
    {/if}
{include file='bottom.tpl'}
