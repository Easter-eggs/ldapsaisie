{if $freeze}
<span class='LSformElement_text LSformElement_maildir'>{if $value}{$value}{else}{$noValueTxt}{/if}</span><input type='hidden' name='{$attr_name}[]' class='LSformElement_text LSformElement_maildir' value="{$value}"/>
{else}
<input type='text' name='{$attr_name}[]' class='LSformElement_text LSformElement_maildir' value="{$value}"/>
{/if}
