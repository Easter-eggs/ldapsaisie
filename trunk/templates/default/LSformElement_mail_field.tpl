{if $freeze}
<span class='LSformElement_text'>{if $value}<a class='LSformElement_mail' href='mailto:{$value}'>{$value}</a>{else}{$noValueTxt}{/if}</span><input type='hidden' name='{$attr_name}[]' class='LSformElement_text' value="{$value}"/>
{else}
<input type='text' name='{$attr_name}[]' class='LSformElement_text LSformElement_mail' value="{$value}"/>
{/if}
