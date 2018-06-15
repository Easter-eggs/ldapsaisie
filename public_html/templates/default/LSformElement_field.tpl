{if $freeze}
{if $value}{$value|escape:"htmlall"}{else}{$noValueTxt|escape:"htmlall"}{/if}
{else}
<input type='text' name='{$attr_name|escape:"htmlall"}[]' value='{$value|escape:"htmlall"}' autocomplete="off"/>
{/if}
