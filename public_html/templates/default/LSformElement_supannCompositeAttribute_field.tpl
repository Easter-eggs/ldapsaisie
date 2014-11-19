{if $freeze}
  {if isset($parseValue)}
  {foreach $components as $c => $cconf}
    {if !isset($parseValue[$c])}{continue}{/if}
    <p>
		<label>{tr msg=$cconf.label} : </label>
		{if !empty($parseValue[$c].label) and $parseValue[$c].label!='no'}
			{assign var=clabel value=$parseValue[$c].label}
			<img src='{img name="supann_label_$clabel"}' alt='[{$clabel}]' title='{$clabel}'/>
		{/if}
		<span title="{$parseValue[$c].value}">{$parseValue[$c].translated}</span>
    </p>
  {/foreach}
  {else}
  {$noValueTxt}
  {/if}
{else}
  {foreach $components as $c => $cconf}
    <p data-component="{$c}">
		<label>{tr msg=$cconf.label}{if $cconf.required}*{/if}  :</label>
		{if $cconf.type=='table' or $cconf.type=='codeEntite'}
			<input type='hidden' name='{$attr_name}__{$c}[]' value="{if $parseValue and $parseValue[$c]}{$parseValue[$c].value}{/if}"/>
			{if $parseValue and !empty($parseValue[$c].label) and $parseValue[$c].label!='no'}
				{assign var=clabel value=$parseValue[$c].label}
				<img src='{img name="supann_label_$clabel"}' alt='[{$clabel}]' title='{$clabel}'/>
			{/if}
			{if $parseValue}
				<span title="{$parseValue[$c].value}">{$parseValue[$c].translated}</span>
			{else}
				<span>{$noValueTxt}</span>
			{/if}
		{else}
			<input type='text' name='{$attr_name}__{$c}[]' value="{if $parseValue and $parseValue[$c]}{$parseValue[$c].value}{/if}"/>
		{/if}
    </p>
  {/foreach}
{/if}
