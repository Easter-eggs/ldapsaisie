{if $freeze}
  {if isset($parseValue)}
    {if $parseValue.label}
      {if $parseValue.translated_label}
        <span title='[{$parseValue.label|escape:'quotes'}]'>{$parseValue.translated_label}</span>
      {else}
        <span>{$parseValue.label} {$unrecognizedLabelTxt}</span>
      {/if}
      : <span>{$parseValue.value}</span>
    {else}
      <span>{$parseValue.raw_value}</span> {$unrecognizedValueTxt}
    {/if}
  {else}
    {$noValueTxt}
  {/if}
{else}
  <select name='{$attr_name}_labels[]' class='LSformElement_labeledValue'>
    {html_options options=$labels selected=$parseValue.label}
  </select>
  <input type="text" name='{$attr_name}_values[]' class='LSformElement_labeledValue' value='{if $parseValue.value}{$parseValue.value|escape:'quotes'}{else}{$parseValue.raw_value|escape:'quotes'}{/if}'/>
{/if}
