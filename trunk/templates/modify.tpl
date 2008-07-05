{include file='top.tpl'}
    {if $pagetitle != ''}<h1>{$pagetitle}</h1>{/if}
    {if $LSview_actions != ''}
    <ul class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <li class='LSview-actions'><a href='{$item.url}' class='LSview-actions'><img src='templates/images/{$item.action}.png' alt='{$item.label}' title='{$item.label}' /> {$item.label}</a></li>
      {/foreach}
    </ul>
    {/if}
    
    {if $LSformElement_image!=''}
    <div class='LSformElement_image{if $LSformElement_image_errors} LSformElement_image_errors{/if}'>
      {if $LSformElement_image_actions!='' && !$LSformElement_image_errors}
      <ul class='LSformElement_image_actions'>
          <li><img src='templates/images/zoom.png' class='LSformElement_image_actions LSformElement_image_action_zoom' id='LSformElement_image_action_zoom_{$LSformElement_image.id}' /></li>
        {foreach from=$LSformElement_image_actions item=item}
          <li><img src='templates/images/{$item}.png' class='LSformElement_image_actions LSformElement_image_action_{$item}' id='LSformElement_image_action_{$item}_{$LSformElement_image.id}' /></li>
        {/foreach}
      </ul>
      {/if}
      <img src='{$LSformElement_image.img}' class='LSformElement_image LSsmoothbox' id='LSformElement_image_{$LSformElement_image.id}' />
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
