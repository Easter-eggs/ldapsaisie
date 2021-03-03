{if $freeze}
<span class='LSformElement_text{if $additionalCssClass} {if is_array($additionalCssClass)}{" "|implode:$additionalCssClass}{else}{$additionalCssClass}{/if}{/if}' value='{if $value}{$value|escape:"htmlall"}{/if}'>{if $value}{$value|escape:"htmlall"}{else}{$noValueTxt|escape:"htmlall"}{/if}</span>
<input type='hidden' name='{$attr_name|escape:"htmlall"}[]' class='LSformElement_text{if $additionalCssClass} {if is_array($additionalCssClass)}{" "|implode:$additionalCssClass}{else}{$additionalCssClass}{/if}{/if}' value='{$value|escape:"htmlall"}'/>
{else}
<input type='text' name='{$attr_name|escape:"htmlall"}[]' class='LSformElement_text{if $additionalCssClass} {if is_array($additionalCssClass)}{" "|implode:$additionalCssClass}{else}{$additionalCssClass}{/if}{/if}' value='{$value|escape:"htmlall"}' autocomplete="off"/>
{/if}
