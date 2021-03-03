{if $freeze}
  <span class='LSformElement_text'>
  {if $value}
    <a class='{if $additionalCssClass} {if is_array($additionalCssClass)}{" "|implode:$additionalCssClass}{else}{$additionalCssClass}{/if}{/if}' value='{$value|escape:"htmlall"}' href='{$uriPrefix|escape:"htmlall"}{$value|escape:"htmlall"}'{if isset($uriLinkTitle) && $uriLinkTitle} title='{$uriLinkTitle|escape:"htmlall"}'{/if}{if isset($uriTarget) && $uriTarget} target='{$uriTarget|escape:"htmlall"}'{/if}>{$value|escape:"htmlall"}</a>
  {else}
    {$noValueTxt|escape:"htmlall"}
  {/if}
  </span>
  <input type='hidden' name='{$attr_name|escape:"htmlall"}[]' class='LSformElement_text{if $additionalCssClass} {if is_array($additionalCssClass)}{" "|implode:$additionalCssClass}{else}{$additionalCssClass}{/if}{/if}' value='{$value|escape:"htmlall"}'/>
{else}
  <input type='text' name='{$attr_name|escape:"htmlall"}[]' class='LSformElement_text{if $additionalCssClass} {if is_array($additionalCssClass)}{" "|implode:$additionalCssClass}{else}{$additionalCssClass}{/if}{/if}' value='{$value|escape:"htmlall"}' autocomplete="off"/>
{/if}
