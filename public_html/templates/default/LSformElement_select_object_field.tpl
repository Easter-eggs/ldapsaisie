{if $dn}
  <a href='view.php?LSobject={$selectableObject|escape:"url"}&amp;dn={$dn|escape:'url'}' class='LSformElement_select_object'>{$txt|escape:"htmlall"}</a>
  {if !$freeze}<input type='hidden' class='LSformElement_select_object' name='{$attr_name|escape:"quotes"}[]' value='{$dn|escape:"quotes"}' />{/if}
{else}
  {$noValueTxt|escape:"htmlall"}
{/if}
