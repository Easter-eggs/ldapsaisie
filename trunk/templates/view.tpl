{include file='top.tpl'}
    {if $pagetitle != ''}<h1>{$pagetitle}</h1>{/if}
    {if $LSview_actions != ''}
    <p class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <a href='{$item.url}' class='LSview-actions'><img src='templates/images/{$item.action}.png' alt='{$item.label}' title='{$item.label}' /> {$item.label}</a>
      {/foreach}
    </p>
    {/if}
    {if $LSform_image!=''}
    <div class='LSform_image'>
      <a href='{$LSform_image.img}'><img src='{$LSform_image.img}' class='LSform_image LSsmoothbox' /></a>
    </div>
    {/if}
    <dl class='LSform'>
      {foreach from=$LSform_fields item=field}
      <dt class='LSform'>{$field.label}</dt>
      <dd class='LSform'>{$field.html}</dd>
      {/foreach}
    </dl>
    
    {if $LSrelations!=''}
      {foreach from=$LSrelations item=item}
        {include file='LSrelations.tpl'}
      {/foreach}
    {/if}
{include file='bottom.tpl'}
