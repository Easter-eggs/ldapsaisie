{if $freeze}
  {if isset($parseValue)}
    {if isset($parseValue.translated_label)}
      <span class='LSformElement_supannLabeledValue_label'>{tr msg=$parseValue.translated_label}&nbsp;:</span>
    {elseif !empty($parseValue.label) and $parseValue.label!='no'}
      {assign var=clabel value=$parseValue.label}
      <img src='{img name="supann_label_$clabel"}' alt='[{$clabel|escape:"htmlall"}]' title='{$clabel|escape:"htmlall"}'/>
    {/if}
    {if $valueFieldType == 'textarea'}
      <pre class='LSformElement_supannLabeledValue_value' {if $nomenclatureTable}title='{$parseValue.value|escape:"htmlall"}'{/if}>{$parseValue.translated|escape:"htmlall"}</pre>
    {else}
      <span {if $nomenclatureTable}title='{$parseValue.value|escape:"htmlall"}'{/if}>{$parseValue.translated|escape:"htmlall"}</span>
    {/if}
  {else}
    {$noValueTxt|escape:"htmlall"}
  {/if}
{else}
  <input type='hidden' name='{$attr_name|escape:"htmlall"}[]' value='{if $parseValue}{$parseValue.value|escape:"htmlall"}{/if}'/>
  {if $nomenclatureTable}
    {if $parseValue and !empty($parseValue.label) and $parseValue.label!='no'}
      {assign var=clabel value=$parseValue.label}
      <img class='LSformElement_supannLabeledValue_label' src='{img name="supann_label_$clabel"}' alt='[{$clabel|escape:"htmlall"}]' title='{$clabel|escape:"htmlall"}'/>
    {/if}
    {if $parseValue}
      <span title='{$parseValue.value|escape:"htmlall"}'>{$parseValue.translated|escape:"htmlall"}</span>
    {else}
      <span>{$noValueTxt|escape:"htmlall"}</span>
    {/if}
  {else}
    {if $possibleLabels}
      <select class='LSformElement_supannLabeledValue_label'>
        {if $parseValue}
          {html_options options=$possibleLabels selected=$parseValue.label}
        {else}
          {html_options options=$possibleLabels}
        {/if}
      </select>
    {else}
      <input type='text' class='LSformElement_supannLabeledValue_label'{if $parseValue} value='{$parseValue.label|escape:"htmlall"}'{/if}/>
    {/if}
    {if $valueFieldType == 'textarea'}
      <textarea class='LSformElement_supannLabeledValue_value'>{if $parseValue}{$parseValue.translated|escape:"htmlall"}{/if}</textarea>
    {else}
      <input type='text' class='LSformElement_supannLabeledValue_value'{if $parseValue} value='{$parseValue.translated|escape:"htmlall"}'{/if}/>
    {/if}
  {/if}
{/if}
