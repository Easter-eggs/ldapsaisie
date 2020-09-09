{if $freeze}
  {if $value}{$value|escape:"htmlall"}{else}{$noValueTxt|escape:"htmlall"}{/if}
{else}
  <input type='text' name='{$attr_name|escape:"htmlall"}[{$value_idx}]' value='{if !in_array($value, $special_values)}{$value|escape:'htmlall'}{/if}' class='LSformElement_date' autocomplete="off">
  {foreach from=$special_values item=special_value_label key=special_value name=LSformElement_date_special_value}
    <input type='radio' name='{$attr_name|escape:"htmlall"}__special_value[{$value_idx}]' class='LSformElement_date' value='{$special_value|escape:"htmlall"}'
    id='LSformElement_date_special_value_{$attr_name|escape:"htmlall"}_{$value_idx}_{$smarty.foreach.LSformElement_date_special_value.index}' {if $value == $special_value_label}checked{/if}/>
    <label for='LSformElement_date_special_value_{$attr_name|escape:"htmlall"}_{$value_idx}_{$smarty.foreach.LSformElement_date_special_value.index}'>{$special_value_label|escape:"htmlall"}</label>
  {/foreach}
{/if}
