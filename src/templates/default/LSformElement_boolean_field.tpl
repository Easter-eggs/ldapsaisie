{if $freeze}
{if $value=='yes'}{$yesTxt|escape:"htmlall"}{elseif $value=='no'}{$noTxt|escape:"htmlall"}{else}{$noValueTxt|escape:"htmlall"}{/if}
{else}
<input type='radio' value='yes' name='{$attr_name|escape:"htmlall"}[0]' {if $value=='yes'}checked="true"{/if} />{$yesTxt|escape:"htmlall"} <input type='radio' value='no' name='{$attr_name|escape:"htmlall"}[0]' {if $value=='no'}checked="true"{/if} /> {$noTxt|escape:"htmlall"}
{/if}
