{if $freeze}
  {if $dn}
    <a href='view.php?LSobject={$selectableObject}&amp;dn={$dn}' class='LSformElement_select_object'>{$txt}</a>
  {else}
    {$noValueTxt}
  {/if}
{else}
  <a href='view.php?LSobject={$selectableObject}&amp;dn={$dn}' class='LSformElement_select_object'>{$txt}</a><input type='hidden' class='LSformElement_select_object' name='{$attr_name}[]' value='{$dn}' />
{/if}
