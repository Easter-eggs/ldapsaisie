{if $freeze}
  {if isset($parseValue)}
    {if !empty($parseValue.label) and $parseValue.label!='no'}
      {assign var=clabel value=$parseValue.label}
      <img src='{img name="supann_label_$clabel"}' alt='[{$clabel}]' title='{$clabel}'/>
    {/if}
    <span title="{$parseValue.value}">{$parseValue.translated}</span>
  {else}
    {$noValueTxt}
  {/if}
{else}
  <input type='hidden' name='{$attr_name}[]' value="{if $parseValue}{$parseValue.value}{/if}"/>
  {if $parseValue and !empty($parseValue.label) and $parseValue.label!='no'}
    {assign var=clabel value=$parseValue.label}
    <img class='LSformElement_supannLabeledValue_label' src='{img name="supann_label_$clabel"}' alt='[{$clabel}]' title='{$clabel}'/>
  {/if}
  {if $parseValue}
    <span title="{$parseValue.value}">{$parseValue.translated}</span>
  {else}
    <span>{$noValueTxt}</span>
  {/if}
{/if}
