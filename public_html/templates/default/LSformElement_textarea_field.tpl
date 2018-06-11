{if $freeze}
{if $value}<p class='LSformElement_textarea{if $LSformElement_textarea_extra} {$LSformElement_textarea_extra|escape:"quotes"}{/if}'>{$value|escape:"htmlall"}</p>{else}{$noValueTxt|escape:"htmlall"}{/if}
{else}
<textarea name='{$attr_name|escape:"quotes"}[]' class='LSform{if $LSformElement_textarea_extra} {$LSformElement_textarea_extra|escape:"quotes"}{/if}'>{$value|escape:"htmlall"}</textarea>
{/if}
