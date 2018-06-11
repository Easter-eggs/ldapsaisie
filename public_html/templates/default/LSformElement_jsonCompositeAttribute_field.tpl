{if $freeze}
  {if isset($parseValue)}
  {foreach $components as $c => $cconf}
    {if !isset($parseValue[$c])}{continue}{/if}
    <div>
		<label>{tr msg=$cconf.label} : </label>
		<ul>
		{if $cconf.multiple && is_array($parseValue[$c])}
			{foreach from=$parseValue[$c] item=cval}
				<li><span title='{$cval.value|escape:"quotes"}'>{$cval.translated|escape:"htmlall"}</span></li>
			{/foreach}
		{else}
			<li><span title='{$parseValue[$c].value|escape:"htmlall"}'>{$parseValue[$c].translated|escape:"htmlall"}</span></li>
		{/if}
		</ul>
    </div>
  {/foreach}
  {else}
  {$noValueTxt|escape:"htmlall"}
  {/if}
{else}
  {uniqid var="uuid"}
  <input type='hidden' name='{$attr_name|escape:"quotes"}__values_uuid[]' value='{$uuid|escape:"quotes"}' />
  {foreach from=$components key=c item=cconf name=components}
    <div data-component='{$c|escape:"quotes"}' data-uuid='{$uuid|escape:"quotes"}'>
		<label>
			{tr msg=$cconf.label}{if $cconf.required}*{/if}
			{if $cconf.help_info}<img class='LStips' src="{img name='help'}" alt='?' title='{$cconf.help_info|escape:"quotes"}'/>{/if}
			:
		</label>
		{if $cconf.type=='select_list'}
			<select name='{$attr_name|escape:"quotes"}__{$c|escape:"quotes"}__{$uuid|escape:"quotes"}[]' {if $cconf.multiple}multiple{/if}>
				{foreach from=$cconf.possible_values key=key item=label}
					{if is_array($label)}
						{if count($label.possible_values)>0}
						<optgroup label='{$label.label|escape:"quotes"}'>
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
						<option value='{$key|escape:"quotes"}' {if $selected == 1}selected{/if}>{$label|escape:"htmlall"}</option>
					{/if}
				{/foreach}
			</select>
		{else}
			<ul>
			{if $cconf.multiple && is_array($parseValue[$c])}
				{foreach from=$parseValue[$c] item=cval}
				<li><input type='text' name='{$attr_name|escape:"quotes"}__{$c|escape:"quotes"}__{$uuid|escape:"quotes"}[]' value='{$cval.value|escape:"quotes"}'/></li>
				{foreachelse}
				<li><input type='text' name='{$attr_name|escape:"quotes"}__{$c|escape:"quotes"}__{$uuid|escape:"quotes"}[]' value=''/></li>
				{/foreach}
			{else}
				<li><input type='text' name='{$attr_name|escape:"quotes"}__{$c|escape:"quotes"}__{$uuid|escape:"quotes"}[]' value='{if $parseValue and $parseValue[$c]}{$parseValue[$c].value|escape:"quotes"}{/if}'/></li>
			{/if}
			</ul>
		{/if}
    </div>
  {/foreach}
{/if}
