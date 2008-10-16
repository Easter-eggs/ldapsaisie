{if $freeze}
{if $value=='yes'}{$yesTxt}{elseif $value=='no'}{$noTxt}{else}{$noValueTxt}{/if}
{else}
<input type='radio' value='yes' name='{$attr_name}[0]' {if $value=='yes'}checked{/if} />{$yesTxt} <input type='radio' value='no' name='{$attr_name}[0]' {if $value=='no'}checked{/if} /> {$noTxt}
{/if}
