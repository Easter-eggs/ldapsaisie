{if $freeze}
  {if isset($parseValue)}
  {foreach $components as $c => $cconf}
    {if !isset($parseValue[$c])}{continue}{/if}
    <p>
    <label>{tr msg=$cconf.label} : </label>
    {if !empty($parseValue[$c].label) and $parseValue[$c].label!='no'}
      {assign var=clabel value=$parseValue[$c].label}
      <img src='{img name="supann_label_$clabel"}' alt='[{$clabel|escape:"htmlall"}]' title='{$clabel|escape:"htmlall"}'/>
    {/if}
    {if $cconf.type=='parrainDN' && $parseValue[$c].type}
      <a href='object/{$parseValue[$c].type}/{$parseValue[$c].value}' title='{$parseValue[$c].value|escape:"htmlall"}'>{$parseValue[$c].translated|escape:"htmlall"}</a>
    {else}
      <span title='{$parseValue[$c].value|escape:"htmlall"}'>{$parseValue[$c].translated|escape:"htmlall"}</span>
    {/if}
    </p>
  {/foreach}
  {else}
  {$noValueTxt|escape:"htmlall"}
  {/if}
{else}
  {foreach $components as $c => $cconf}
    <p data-component='{$c|escape:"htmlall"}'>
    <label>{tr msg=$cconf.label}{if $cconf.required}*{/if}  :</label>
    {if $cconf.type=='table' or $cconf.type=='codeEntite' or $cconf.type=='parrainDN'}
      <input type='hidden' name='{$attr_name|escape:"htmlall"}__{$c|escape:"htmlall"}[]' value='{if $parseValue and $parseValue[$c]}{$parseValue[$c].value|escape:"htmlall"}{/if}'/>
      {if $parseValue and !empty($parseValue[$c].label) and $parseValue[$c].label!='no'}
        {assign var=clabel value=$parseValue[$c].label}
        <img src='{img name="supann_label_$clabel"}' alt='[{$clabel|escape:"htmlall"}]' title='{$clabel|escape:"htmlall"}'/>
      {/if}
      {if $parseValue && $parseValue[$c]}
        <span title='{$parseValue[$c].value|escape:"htmlall"}'>{$parseValue[$c].translated|escape:"htmlall"}</span>
      {else}
        <span>{$noValueTxt|escape:"htmlall"}</span>
      {/if}
    {elseif $cconf.type=='select'}
      <select name='{$attr_name|escape:"htmlall"}__{$c|escape:"htmlall"}[]'>
        {if $parseValue}
          {html_options options=$cconf.possible_values selected=$parseValue[$c].value}
        {else}
          {html_options options=$cconf.possible_values}
        {/if}
      </select>
    {elseif $cconf.type=='date' or $cconf.type=='datetime'}
      <input type='text' class='LSformElement_date' name='{$attr_name|escape:"htmlall"}__{$c|escape:"htmlall"}[]' value='{if $parseValue and $parseValue[$c]}{$parseValue[$c].translated|escape:"htmlall"}{/if}'/>
    {else}
      <input type='text' name='{$attr_name|escape:"htmlall"}__{$c|escape:"htmlall"}[]' value='{if $parseValue and $parseValue[$c]}{$parseValue[$c].value|escape:"htmlall"}{/if}'/>
    {/if}
    </p>
  {/foreach}
{/if}
