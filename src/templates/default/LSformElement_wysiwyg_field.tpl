<div class='LSformElement_wysiwyg_container'>
{if $freeze}
{if $value}<div class='LSformElement_wysiwyg'>{$value}</div>{else}{$noValueTxt|escape:"htmlall"}{/if}
{else}
<textarea name='{$attr_name|escape:"quotes"}[]' class='LSform LSformElement_wysiwyg'>{$value|escape:"htmlall"}</textarea>
{/if}
</div>
