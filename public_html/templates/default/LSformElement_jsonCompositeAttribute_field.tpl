{if $freeze}
  {if isset($parseValue)}
  {foreach $components as $c => $cconf}
    {if !isset($parseValue[$c])}{continue}{/if}
    <p>
		<label>{tr msg=$cconf.label} : </label>
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
		{if $cconf.type=='select_list'}
			<select name='{$attr_name}__{$c}[]'>
				{foreach from=$cconf.possible_values key=key item=label}
					{if is_array($label)}
						{if count($label.possible_values)>0}
						<optgroup label="{$label.label}">
							{html_options options=$label.possible_values selected=$parseValue[$c].value}
						</optgroup>
						{/if}
					{else}
						<option value="{$key}" {if $key == $parseValue[$c].value}selected{/if}>{$label}</option>
					{/if}
				{/foreach}
			</select>
		{else}
			<input type='text' name='{$attr_name}__{$c}[]' value="{if $parseValue and $parseValue[$c]}{$parseValue[$c].value}{/if}"/>
		{/if}
    </p>
  {/foreach}
{/if}
