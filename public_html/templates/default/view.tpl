{include file='ls:top.tpl'}
    {if $pagetitle != ''}<h1 id='LSview_title'>{$pagetitle|escape:"htmlall"}</h1>{/if}
    {if $LSview_actions != ''}
    <ul class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        {if is_array($item)}
        <li class='LSview-actions'><a href='{$item.url|escape:"quotes"}' class='LSview-actions{if $item.class} {$item.class|escape:"quotes"}{/if}{if $item.helpInfo || ($item.hideLabel && $item.label)} LStips{/if}' {if $item.helpInfo || ($item.hideLabel && $item.label)}title='{if $item.helpInfo}{$item.helpInfo|escape:"htmlall"}{else}{$item.label|escape:"htmlall"}{/if}'{/if}><img src="{img name=$item.action}" alt='{$item.label|escape:"htmlall"}' title='{$item.label|escape:"htmlall"}' />{if !isset($item.hideLabel) || !$item.hideLabel} {$item.label}{/if}</a></li>
        {/if}
      {/foreach}
    </ul>
    {/if}
    
    {include file='ls:LSform_view.tpl'}
    
    {if $LSrelations!=''}
      {foreach from=$LSrelations item=item}
        {include file='ls:LSrelations.tpl'}
      {/foreach}
    {/if}
{include file='ls:bottom.tpl'}
