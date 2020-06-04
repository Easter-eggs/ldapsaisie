{if isset($LSview_actions) && is_array($LSview_actions) && !empty($LSview_actions)}
<ul class='LSview-actions'>
  {foreach from=$LSview_actions item=item}
    {if is_array($item)}
      <li class='LSview-actions{if isset($item['hideLabel']) && $item.hideLabel} LSview-actions-hidden-label{/if}'>
        <a
          href='{$item.url}'
          class='
            LSview-actions
            {if isset($item['class'])}{$item.class|escape:"quotes"}{/if}
            {if isset($item['helpInfo']) || (isset($item['hideLabel']) && $item.hideLabel && isset($item.label) && $item.label)} LStips{/if}
          '
          {if isset($item['helpInfo']) || (isset($item['hideLabel']) && $item.hideLabel && isset($item.label) && $item.label)}title='{if $item.helpInfo}{$item.helpInfo|escape:"htmlall"}{else}{$item.label|escape:"htmlall"}{/if}'{/if}
          {if isset($item['id']) && $item.id}id='{$item.id|escape:"quotes"}'{/if}
          {if isset($item['data']) && is_array($item['data']) && !empty($item['data'])}
          {foreach $item['data'] as $data_key => $data_value}
            data-{$data_key}='{$data_value|escape:'htmlall'}'
          {/foreach}
          {/if}
        >
          <img src="{img name=$item.action}" alt='{$item.label|escape:"htmlall"}' title='{$item.label|escape:"htmlall"}' />
          <span>{$item.label}</span>
        </a>
      </li>
    {/if}
  {/foreach}
</ul>
{/if}
