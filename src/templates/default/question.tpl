{extends file='ls:base_connected.tpl'}
{block name="content"}
    {if $pagetitle != ''}<h1>{$pagetitle|escape:"htmlall"}</h1>{/if}
    {if $LSview_actions != ''}
    <p class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        <a href='{$item.url}' class='LSview-actions'><img src='{img name=$item.action}' alt='{$item.label|escape:"htmlall"}' title='{$item.label|escape:"htmlall"}' /></a>
      {/foreach}
    </p>
    {/if}

    <p class='question'>{$question}</p>
    <a href='{$validation_url}' class='question'>{$validation_label|escape:"htmlall"}</a>
{/block}
