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
		<span title='{$parseValue[$c].value|escape:"htmlall"}'>{$parseValue[$c].translated|escape:"htmlall"}</span>
    </p>
  {/foreach}
  {else}
  {$noValueTxt|escape:"htmlall"}
  {/if}
{else}
  {foreach $components as $c => $cconf}
    <p data-component='{$c|escape:"htmlall"}'>
		<label>{tr msg=$cconf.label}{if $cconf.required}*{/if}  :</label>
		{if $cconf.type=='table' or $cconf.type=='codeEntite'}
			<input type='hidden' name='{$attr_name|escape:"htmlall"}__{$c|escape:"htmlall"}[]' value='{if $parseValue and $parseValue[$c]}{$parseValue[$c].value|escape:"htmlall"}{/if}'/>
			{if $parseValue and !empty($parseValue[$c].label) and $parseValue[$c].label!='no'}
				{assign var=clabel value=$parseValue[$c].label}
				<img src='{img name="supann_label_$clabel"}' alt='[{$clabel|escape:"htmlall"}]' title='{$clabel|escape:"htmlall"}'/>
			{/if}
			{if $parseValue}
				<span title='{$parseValue[$c].value|escape:"htmlall"}'>{$parseValue[$c].translated|escape:"htmlall"}</span>
			{else}
				<span>{$noValueTxt|escape:"htmlall"}</span>
			{/if}
		{else}
			<input type='text' name='{$attr_name|escape:"htmlall"}__{$c|escape:"htmlall"}[]' value='{if $parseValue and $parseValue[$c]}{$parseValue[$c].value|escape:"htmlall"}{/if}'/>
		{/if}
    </p>
  {/foreach}
{/if}
