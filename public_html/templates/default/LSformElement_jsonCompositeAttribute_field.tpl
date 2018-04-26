{if $freeze}
  {if isset($parseValue)}
  {foreach $components as $c => $cconf}
    {if !isset($parseValue[$c])}{continue}{/if}
    <div>
		<label>{tr msg=$cconf.label} : </label>
		<ul>
		{if $cconf.multiple && is_array($parseValue[$c])}
			{foreach from=$parseValue[$c] item=$cval}
				<li><span title="{$cval.value}">{$cval.translated}</span></li>
			{/foreach}
		{else}
			<li><span title="{$parseValue[$c].value}">{$parseValue[$c].translated}</span></li>
		{/if}
		</ul>
    </div>
  {/foreach}
  {else}
  {$noValueTxt}
  {/if}
{else}
  {uniqid var="uuid"}
  <input type='hidden' name="{$attr_name}__values_uuid[]" value="{$uuid}" />
  {foreach from=$components key=$c item=$cconf name=components}
    <div data-component="{$c}" data-uuid="{$uuid}">
		<label>
			{tr msg=$cconf.label}{if $cconf.required}*{/if}
			{if $cconf.help_info}<img class='LStips' src="{img name='help'}" alt='?' title="{$cconf.help_info}"/>{/if}
			:
		</label>
		{if $cconf.type=='select_list'}
			<select name='{$attr_name}__{$c}__{$uuid}[]' {if $cconf.multiple}multiple{/if}>
				{foreach from=$cconf.possible_values key=key item=label}
					{if is_array($label)}
						{if count($label.possible_values)>0}
						<optgroup label="{$label.label}">
							{if $cconf.multiple && is_array($parseValue[$c])}
								{html_options options=$label.possible_values selected=$parseValue[$c]}
							{else}
								{html_options options=$label.possible_values selected=$parseValue[$c].value}
							{/if}
						</optgroup>
						{/if}
					{else}
						{assign var="selected" value=0}
						{if $cconf.multiple && is_array($parseValue[$c])}
							{foreach from=$parseValue[$c] item=cval}
								{if $key==$cval.value}{assign var="selected" value=1}{/if}
							{/foreach}
						{else}
							{if $key == $parseValue[$c].value}
								{assign var="selected" value=1}
							{/if}
						{/if}
						<option value="{$key}" {if $selected == 1}selected{/if}>{$label}</option>
					{/if}
				{/foreach}
			</select>
		{else}
			<ul>
			{if $cconf.multiple && is_array($parseValue[$c])}
				{foreach from=$parseValue[$c] item=$cval}
				<li><input type='text' name='{$attr_name}__{$c}__{$uuid}[]' value='{$cval.value|escape:"quotes"}'/></li>
				{foreachelse}
				<li><input type='text' name='{$attr_name}__{$c}__{$uuid}[]' value=''/></li>
				{/foreach}
			{else}
				<li><input type='text' name='{$attr_name}__{$c}__{$uuid}[]' value='{if $parseValue and $parseValue[$c]}{$parseValue[$c].value|escape:"quotes"}{/if}'/></li>
			{/if}
			</ul>
		{/if}
    </div>
  {/foreach}
{/if}
