<ul class='LSform {if $multiple && !$freeze} LSformElement_multiple{/if} LSformElement_supannLabeledValue' id='{$attr_name}' data-fieldType="{$fieldType}">
	{foreach from=$parseValues item=parseValue}
	  <li>{include file="ls:$fieldTemplate"}</li>
	{foreachelse}
	  <li {if $freeze}class='noValue'{/if}>{include file="ls:$fieldTemplate"}</li>
	{/foreach}
</ul>
