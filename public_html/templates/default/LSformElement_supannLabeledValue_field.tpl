{if $freeze}
  {if $value or $parseValue}
    {if $parseValue}
      <span class='LSformElement_supannLabeledValue'>{if $label}<img src='{img name="supann_label_$label"}' alt='[{$label}]' title='{$label}' /> {/if}<span title='{$parseValue.value}'>{$value}</span></span> 
    {else}
      <span class='LSformElement_supannLabeledValue_unparsed'>{$value}</span>
    {/if}
  {else}
    {$noValueTxt}
  {/if}
{else}
{if $parseValue}
  <input type='text' name='{$attr_name}[]' class='LSformElement_text' value="{$parseValue.value}" autocomplete="off"/>
{else}
  <input type='text' name='{$attr_name}[]' class='LSformElement_text' value="{$value}" autocomplete="off"/>
{/if}
{/if}
