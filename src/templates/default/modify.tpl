{extends file='ls:base_connected.tpl'}
{block name="content"}
    {if $pagetitle != ''}<h1 id='LSform_title' data-object-name="{$object_name}">{$pagetitle|escape:"htmlall"}</h1>{/if}

    {include file='ls:LSview_actions.tpl'}

    {include file='ls:LSform.tpl'}
{/block}
