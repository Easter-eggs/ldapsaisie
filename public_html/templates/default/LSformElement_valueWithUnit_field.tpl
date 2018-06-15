{if $freeze}
  {if $value}
    {if $values_and_units[$value].unknown}
      <span class='LSformElement_valueWithUnit_unknown'>{$values_and_units[$value].unknown|escape:"htmlall"}</span>
    {else}
      {if $values_and_units[$value].valueWithUnit}
        {$values_and_units[$value].valueWithUnit|escape:"htmlall"}{$values_and_units[$value].unitLabel|escape:"htmlall"}
      {else}
        {$values_and_units[$value].value|escape:"htmlall"}
      {/if}
    {/if}
  {else}
    {$noValueTxt|escape:"htmlall"}
  {/if}
{else}
  {if $values_and_units[$value].valueWithUnit || !$values_and_units[$value]}
    <input name='{$attr_name|escape:"htmlall"}_valueWithUnit[]' type=text class='LSformElement_valueWithUnit' value='{$values_and_units[$value].valueWithUnit|escape:"htmlall"}'/>
    <select name='{$attr_name|escape:"htmlall"}_unitFact[]' class='LSform LSformElement_valueWithUnit'>
      {html_options options=$units selected=$values_and_units[$value].unitSill}
    </select>
  {else}
    <input name='{$attr_name|escape:"htmlall"}_value[]' type=text class='LSformElement_valueWithUnit' value='{$values_and_units[$value].value|escape:"htmlall"}' autocomplete="off"/>
  {/if}
  {if $values_and_units[$value].unknown}
    <span class='LSformElement_valueWithUnit_unknown'>{$values_and_units[$value].unknown|escape:"htmlall"}</span>
  {/if}
{/if}
