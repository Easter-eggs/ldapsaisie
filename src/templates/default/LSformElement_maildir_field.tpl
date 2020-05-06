{if $freeze}
<span class='LSformElement_text LSformElement_maildir'>{if $value}{$value|escape:"htmlall"}{else}{$noValueTxt|escape:"htmlall"}{/if}</span><input type='hidden' name='{$attr_name|escape:"htmlall"}[]' class='LSformElement_text LSformElement_maildir' value='{$value|escape:"htmlall"}'/>
{else}
<input type='text' name='{$attr_name|escape:"htmlall"}[]' class='LSformElement_text LSformElement_maildir' value='{$value|escape:"htmlall"}' autocomplete="off"/>
{/if}
