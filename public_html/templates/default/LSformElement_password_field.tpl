{if $freeze}
{if $clearView}
{$pwd}
{else}
********
{/if}
{else}
{if $clearEdit}
<input type='text' name='{$attr_name}[]' value="{$pwd}" class='LSformElement_password' autocomplete="off"/>
{else}
<input type='password' name='{$attr_name}[]' value="{$pwd}" class='LSformElement_password' autocomplete="off"/>
{/if}
{/if}
