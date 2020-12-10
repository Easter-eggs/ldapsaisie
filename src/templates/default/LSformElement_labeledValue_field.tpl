{if $freeze}
  {if isset($parseValue)}
    {if $parseValue.label}
      {if isset($labels[$parseValue.label])}
        <span title='[{$parseValue.label|escape:'htmlall'}]'>{$labels[$parseValue.label]|escape:"htmlall"}</span>
      {else}
        <span>{$parseValue.label|escape:"htmlall"} {$unrecognizedLabelTxt|escape:"htmlall"}</span>
      {/if}
      : <span>{$parseValue.value|escape:"htmlall"}</span>
    {else}
      <span>{$parseValue.raw_value|escape:"htmlall"}</span> {$unrecognizedValueTxt|escape:"htmlall"}
    {/if}
  {else}
    {$noValueTxt|escape:"htmlall"}
  {/if}
{else}
  <select name='{$attr_name|escape:"htmlall"}_labels[]' class='LSformElement_labeledValue'>
    {html_options options=$labels selected=$parseValue.label}
  </select><input type="text" name='{$attr_name|escape:"htmlall"}_values[]' class='LSformElement_labeledValue' value='{if $parseValue.value}{$parseValue.value|escape:'htmlall'}{else}{$parseValue.raw_value|escape:'htmlall'}{/if}'/>
{/if}
