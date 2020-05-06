{include file='ls:top.tpl'}
    {if $pagetitle != ''}<h1 id='LSview_title'>{$pagetitle|escape:"htmlall"}</h1>{/if}
    {if $LSview_actions != ''}
    <ul class='LSview-actions'>
      {foreach from=$LSview_actions item=item}
        {if is_array($item)}
        <li class='LSview-actions'>
          <a
            href='{$item.url}'
            class='
              LSview-actions
              {if isset($item['class'])}{$item.class|escape:"quotes"}{/if}
              {if isset($item['helpInfo']) || (isset($item['hideLabel']) && $item.hideLabel && isset($item.label) && $item.label)} LStips{/if}
            '
            {if isset($item['helpInfo']) || (isset($item['hideLabel']) && $item.hideLabel && isset($item.label) && $item.label)}title='{if $item.helpInfo}{$item.helpInfo|escape:"htmlall"}{else}{$item.label|escape:"htmlall"}{/if}'{/if}
          >
            <img src="{img name=$item.action}" alt='{$item.label|escape:"htmlall"}' title='{$item.label|escape:"htmlall"}' />
            {if !isset($item.hideLabel) || !$item.hideLabel} {$item.label}{/if}
          </a>
        </li>
        {/if}
      {/foreach}
    </ul>
    {/if}

    {include file='ls:LSform_view.tpl'}

    {if isset($LSrelations) && $LSrelations}
      {foreach from=$LSrelations item=item}
        {include file='ls:LSrelations.tpl'}
      {/foreach}
    {/if}
{include file='ls:bottom.tpl'}
