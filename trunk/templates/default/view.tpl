{include file='top.tpl'}
    {if $pagetitle != ''}<h1>{$pagetitle}</h1>{/if}
    {if $LSview_actions != ''}
    <ul class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <li class='LSview-actions'><a href='{$item.url}' class='LSview-actions' ><img src='{$LS_IMAGES_DIR}/{$item.action}.png' alt='{$item.label}' title='{$item.label}' /> {$item.label}</a></li>
      {/foreach}
    </ul>
    {/if}
    {if $LSformElement_image!=''}
    <div class='LSformElement_image'>
      <a href='{$LSformElement_image.img}.png' rel='rien ici' title='comment' class='mb'><img src='{$LSformElement_image.img}' class='LSformElement_image LSsmoothbox' id='LSformElement_image_{$LSformElement_image.id}' /></a>
    </div>
    {/if}
    <input type='hidden' name='LSform_objecttype' id='LSform_objecttype'  value='{$LSform_object.type}'/>
    <input type='hidden' name='LSform_objectdn' id='LSform_objectdn'  value='{$LSform_object.dn}'/>
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
