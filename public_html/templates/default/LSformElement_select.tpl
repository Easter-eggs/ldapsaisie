<ul class='LSform' id='{$attr_name}'>
{if $freeze}
  {foreach from=$values item=value}
    {LSformElement_select_checkIsValidValue value=$value possible_values=$possible_values}
    {if $LSformElement_select_isValidValue}
    <li>{$LSformElement_select_isValidValue_label}</li>
    {else}
    <li class='LSform-errors'>{getFData format=$unrecognized_value_label_format data=$value}</li>
    {/if}
  {foreachelse}
    <li>{$noValueTxt}</li>
  {/foreach}
{else}
  <li>
    <select name='{$attr_name}[]' {if $multiple}multiple{/if} class='LSformElement_select'>
      {foreach from=$possible_values key=key item=label}
        {if is_array($label)}
          {if count($label.possible_values)>0}
          <optgroup label="{$label.label}">
            {html_options options=$label.possible_values selected=$values}
          </optgroup>
          {/if}
        {else}
          <option value="{$key}" {if in_array($key,$values)}selected{/if}>{$label}</option>
        {/if}
      {/foreach}
    </select>
  </li>
{/if}
</ul>
