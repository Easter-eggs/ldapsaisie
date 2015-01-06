{include file='ls:top.tpl'}
    {if $pagetitle != ''}<h1 id='LSview_title'>{$pagetitle}</h1>{/if}
    {if $LSview_actions != ''}
    <ul class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        {if is_array($item)}
        <li class='LSview-actions'><a href="{$item.url}" class="LSview-actions{if $item.class} {$item.class}{/if}" ><img src="{img name=$item.action}" alt="{$item.label}" title="{$item.label}" />{if !isset($item.hideLabel) || !$item.hideLabel} {$item.label}{/if}</a></li>
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
