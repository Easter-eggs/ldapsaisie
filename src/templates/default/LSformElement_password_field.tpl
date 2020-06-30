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
{if $confirmInput}
<div class="LSformElement_password_confirm">
<label for="{$attr_name|escape:"htmlall"}_confirm[]">{tr msg="Please confirm new password:"}</label>
<input type='password' name='{$attr_name|escape:"htmlall"}_confirm[]' autocomplete="off"/>
{/if}
{/if}
{/if}
