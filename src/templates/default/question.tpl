{extends file='ls:base_connected.tpl'}
{block name="content"}
    {if $pagetitle != ''}<h1>{$pagetitle|escape:"htmlall"}</h1>{/if}
    {include file='ls:LSview_actions.tpl'}

    <p class='question'>{$question}</p>
    <a href='{$validation_url}' class='question'>{$validation_label|escape:"htmlall"}</a>
{/block}
