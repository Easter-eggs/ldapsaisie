{extends file='ls:base_connected.tpl'}
{block name="content"}
    {if $pagetitle != ''}<h1 id='LSview_title'>{$pagetitle|escape:"htmlall"}</h1>{/if}
    
    {include file='ls:LSview_actions.tpl'}

    {include file='ls:LSform_view.tpl'}

    {if isset($LSrelations) && $LSrelations}
      {foreach from=$LSrelations item=item}
        {include file='ls:LSrelations.tpl'}
      {/foreach}
    {/if}
{/block}
