{if $freeze}
  {if isset($parseValue)}
    {if !empty($parseValue.label) and $parseValue.label!='no'}
      {assign var=clabel value=$parseValue.label}
      <img src='{img name="supann_label_$clabel"}' alt='[{$clabel|escape:"htmlall"}]' title='{$clabel|escape:"htmlall"}'/>
    {/if}
    <span title='{$parseValue.value|escape:"htmlall"}'>{$parseValue.translated|escape:"htmlall"}</span>
  {else}
    {$noValueTxt|escape:"htmlall"}
  {/if}
{else}
  <input type='hidden' name='{$attr_name|escape:"htmlall"}[]' value='{if $parseValue}{$parseValue.value|escape:"htmlall"}{/if}'/>
  {if $parseValue and !empty($parseValue.label) and $parseValue.label!='no'}
    {assign var=clabel value=$parseValue.label}
    <img class='LSformElement_supannLabeledValue_label' src='{img name="supann_label_$clabel"}' alt='[{$clabel|escape:"htmlall"}]' title='{$clabel|escape:"htmlall"}'/>
  {/if}
  {if $parseValue}
    <span title='{$parseValue.value|escape:"htmlall"}'>{$parseValue.translated|escape:"htmlall"}</span>
  {else}
    <span>{$noValueTxt|escape:"htmlall"}</span>
  {/if}
{/if}
