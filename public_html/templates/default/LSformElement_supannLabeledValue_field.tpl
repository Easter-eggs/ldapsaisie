{if $freeze}
  {if $value or $parseValue}
    {if $parseValue}
      <span class='LSformElement_supannLabeledValue'>{if $label}<img src='{$LS_IMAGES_DIR}/LSformElement_supannLabeledValue_label_{$label}.png' alt='[{$label}]' title='{$label}' /> {/if}<span title='{$parseValue.value}'>{$value}</span></span> 
    {else}
      <span class='LSformElement_supannLabeledValue_unparsed'>{$value}</span>
    {/if}
  {else}
    {$noValueTxt}
  {/if}
{else}
<input type='text' name='{$attr_name}[]' class='LSformElement_text' value="{$value}" autocomplete="off"/>
{/if}
