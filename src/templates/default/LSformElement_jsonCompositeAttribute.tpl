<ul class='LSform {if $multiple && !$freeze} LSformElement_multiple{/if} LSformElement_jsonCompositeAttribute{if $fullWidth} LSformElement_jsonCompositeAttribute_fullWidth{/if}' id='{$attr_name|escape:"quotes"}' data-fieldType='{$fieldType|escape:"quotes"}'>
	{foreach from=$parseValues item=parseValue}
	  <li>{include file="ls:$fieldTemplate"}</li>
	{foreachelse}
	  <li {if $freeze}class='noValue'{/if}>{include file="ls:$fieldTemplate"}</li>
	{/foreach}
</ul>
