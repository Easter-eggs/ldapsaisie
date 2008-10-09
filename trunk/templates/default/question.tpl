{include file='top.tpl'}
    {if $pagetitle != ''}<h1>{$pagetitle}</h1>{/if}
    {if $LSview_actions != ''}
    <p class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <a href='{$item.url}' class='LSview-actions'><img src='{$LS_IMAGES_DIR}/{$item.action}.png' alt='{$item.label}' title='{$item.label}' /></a>
      {/foreach}
    </p>
    {/if}
    
    <p class='question'>{$question}</p>
    {if $validation_txt!=''}<a href='{$validation_url}' class='question'>Valider</a>{/if}
{include file='bottom.tpl'}
