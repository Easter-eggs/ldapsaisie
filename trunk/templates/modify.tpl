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
    <div class='LSform_image{if $LSform_image_errors} LSform_image_errors{/if}'>
      {if $LSform_image_actions!='' && !$LSform_image_errors}
      <ul class='LSform_image_actions'>
          <li><img src='templates/images/zoom.png' class='LSform_image_actions LSform_image_action_zoom' id='LSform_image_action_zoom_{$LSform_image.id}' /></li>
        {foreach from=$LSform_image_actions item=item}
          <li><img src='templates/images/{$item}.png' class='LSform_image_actions LSform_image_action_{$item}' id='LSform_image_action_{$item}_{$LSform_image.id}' /></li>
        {/foreach}
      </ul>
      {/if}
      <img src='{$LSform_image.img}' class='LSform_image LSsmoothbox' />
    </div>
    {/if}
    
    <form action='{$LSform_action}' method='post' enctype="multipart/form-data" class='LSform'>
    {$LSform_header}
    <dl class='LSform'>
      {foreach from=$LSform_fields item=field}
      <dt class='LSform'>{$field.label}</dt>
      <dd class='LSform'>{$field.html}{if $field.add != ''} <span class='LSform-addfield'>+ Ajouter un champ</span>{/if}</dd>
      {if $field.errors != ''}
        {foreach from=$field.errors item=error}
        <dd class='LSform LSform-errors'>{$error}</dd>
        {/foreach}
      {/if}
      {/foreach}
      <dd class='LSform'><input type='submit' value='{$LSform_submittxt}' class='LSform' /></dd>
    </dl>
    </form>
{include file='bottom.tpl'}
