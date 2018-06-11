{if $freeze}
  <span class='LSformElement_text'>
  {if $value}
    <a class='{$uriClass|escape:"quotes"}' href='{$uriPrefix|escape:"quotes"}{$value|escape:"quotes"}'{if $uriLinkTitle} title='{$uriLinkTitle|escape:"htmlall"}'{/if}{if $uriTarget} target='{$uriTarget|escape:"quotes"}'{/if}>{$value|escape:"htmlall"}</a>
  {else}
    {$noValueTxt|escape:"htmlall"}
  {/if}
  </span>
  <input type='hidden' name='{$attr_name|escape:"quotes"}[]' class='LSformElement_text' value='{$value|escape:"quotes"}'/>
{else}
  <input type='text' name='{$attr_name|escape:"quotes"}[]' class='LSformElement_text {$uriClass|escape:"quotes"}' value='{$value|escape:"quotes"}' autocomplete="off"/>
{/if}
