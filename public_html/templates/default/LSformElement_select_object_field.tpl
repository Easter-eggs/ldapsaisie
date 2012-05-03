{if $dn}
  <a href='view.php?LSobject={$selectableObject}&amp;dn={$dn|escape:'url'}' class='LSformElement_select_object'>{$txt}</a>
  {if !$freeze}<input type='hidden' class='LSformElement_select_object' name='{$attr_name}[]' value='{$dn}' />{/if}
{else}
  {$noValueTxt}
{/if}
