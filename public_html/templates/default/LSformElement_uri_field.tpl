{if $freeze}
  <span class='LSformElement_text'>
  {if $value}
    <a class='{$uriClass|escape:"htmlall"}' href='{$uriPrefix|escape:"htmlall"}{$value|escape:"htmlall"}'{if $uriLinkTitle} title='{$uriLinkTitle|escape:"htmlall"}'{/if}{if $uriTarget} target='{$uriTarget|escape:"htmlall"}'{/if}>{$value|escape:"htmlall"}</a>
  {else}
    {$noValueTxt|escape:"htmlall"}
  {/if}
  </span>
  <input type='hidden' name='{$attr_name|escape:"htmlall"}[]' class='LSformElement_text' value='{$value|escape:"htmlall"}'/>
{else}
  <input type='text' name='{$attr_name|escape:"htmlall"}[]' class='LSformElement_text {$uriClass|escape:"htmlall"}' value='{$value|escape:"htmlall"}' autocomplete="off"/>
{/if}
