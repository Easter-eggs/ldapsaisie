{if $freeze}
{if $clearView}
{$pwd|escape:"htmlall"}
{else}
********
{/if}
{else}
{if $clearEdit}
<input type='text' name='{$attr_name|escape:"htmlall"}[]' value='{$pwd|escape:"htmlall"}' class='LSformElement_password' autocomplete="off"/>
{else}
<input type='password' name='{$attr_name|escape:"htmlall"}[]' value='{$pwd|escape:"htmlall"}' class='LSformElement_password' autocomplete="off"/>
{/if}
{/if}