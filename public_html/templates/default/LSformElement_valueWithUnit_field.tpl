{if $freeze}
  {if $value}
    {if $values_and_units[$value].unknown}
      <span class='LSformElement_valueWithUnit_unknown'>{$values_and_units[$value].unknown}</span>
    {else}
      {if $values_and_units[$value].valueWithUnit}
        {$values_and_units[$value].valueWithUnit}{$values_and_units[$value].unitLabel}
      {else}
        {$values_and_units[$value].value}
      {/if}
    {/if}
  {else}
    {$noValueTxt}
  {/if}
{else}
  {if $values_and_units[$value].valueWithUnit || !$values_and_units[$value]}
    <input name='{$attr_name}_valueWithUnit[]' type=text class='LSformElement_valueWithUnit' value="{$values_and_units[$value].valueWithUnit}"/>
    <select name='{$attr_name}_unitFact[]' class='LSform LSformElement_valueWithUnit'>
      {html_options options=$units selected=$values_and_units[$value].unitSill}
    </select>
  {else}
    <input name='{$attr_name}_value[]' type=text class='LSformElement_valueWithUnit' value="{$values_and_units[$value].value}" autocomplete="off"/>
  {/if}
  {if $values_and_units[$value].unknown}
    <span class='LSformElement_valueWithUnit_unknown'>{$values_and_units[$value].unknown}</span>
  {/if}
{/if}
