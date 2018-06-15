{if $freeze}
  {if $value.type}
    <span class='LSformElement_ssh_key_short_display' title='{$span_title|escape:"htmlall"}'>{$value.shortTxt|escape:"htmlall"}...</span> (Type : {$value.type|escape:"htmlall"}) <a href='mailto:{$value.mail|escape:"hex"}'>{$value.mail|escape:"mail"}</a><p class='LSformElement_ssh_key_value'>{$value.value|escape:"htmlall"}</p>
  {elseif $value.shortTxt}
    <span class='LSformElement_ssh_key_short_display'>{$value.shortTxt|escape:"htmlall"}...</span> ({$unknowTypeTxt|escape:"htmlall"})<p class='LSformElement_ssh_key_value'>{$value.value|escape:"htmlall"}</p>
  {else}
    {$noValueTxt|escape:"htmlall"}
  {/if}
{else}
  <textarea name='{$attr_name|escape:"quotes"}[]' class='LSform LSformElement_ssh_key'>{$value|escape:"htmlall"}</textarea>
{/if}
