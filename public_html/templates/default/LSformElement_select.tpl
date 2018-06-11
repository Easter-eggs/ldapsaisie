<ul class='LSform' id='{$attr_name|escape:"quotes"}'>
{if $freeze}
  {foreach from=$values item=value}
    {LSformElement_select_checkIsValidValue value=$value possible_values=$possible_values}
    {if $LSformElement_select_isValidValue}
    <li>{$LSformElement_select_isValidValue_label|escape:"htmlall"}</li>
    {else}
    <li class='LSform-errors'>{getFData format=$unrecognized_value_label_format data=$value}</li>
    {/if}
  {foreachelse}
    <li>{$noValueTxt|escape:"htmlall"}</li>
  {/foreach}
{else}
  <li>
    <select name='{$attr_name|escape:"quotes"}[]' {if $multiple}multiple{/if} class='LSformElement_select'>
      {foreach from=$possible_values key=key item=label}
        {if is_array($label)}
          {if count($label.possible_values)>0}
          <optgroup label='{$label.label|escape:"quotes"}'>
            {html_options options=$label.possible_values selected=$values}
          </optgroup>
          {/if}
        {else}
          <option value='{$key|escape:"quotes"}' {if in_array($key,$values)}selected{/if}>{$label|escape:"htmlall"}</option>
        {/if}
      {/foreach}
    </select>
  </li>
{/if}
</ul>
