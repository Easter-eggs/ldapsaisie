{if $freeze}
{if $value}{$value}{else}{$noValueTxt}{/if}
{else}
<textarea name='{$attr_name}[]' class='LSform'>{$value}</textarea>
{/if}
