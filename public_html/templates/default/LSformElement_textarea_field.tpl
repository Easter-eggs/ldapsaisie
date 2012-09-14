{if $freeze}
{if $value}<p class='LSformElement_textarea{if $LSformElement_textarea_extra} {$LSformElement_textarea_extra}{/if}'>{$value}</p>{else}{$noValueTxt}{/if}
{else}
<textarea name='{$attr_name}[]' class='LSform{if $LSformElement_textarea_extra} {$LSformElement_textarea_extra}{/if}'>{$value}</textarea>
{/if}
