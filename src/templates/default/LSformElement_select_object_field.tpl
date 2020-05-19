{if $dn}
  <a href='object/{$info.object_type|escape:"url"}/{$dn|escape:'url'}' class='LSformElement_select_object'>{$info.name|escape:"htmlall"}</a>
  {if !$freeze}<input type='hidden' class='LSformElement_select_object' name='{$attr_name|escape:"htmlall"}[]' value='{$dn|escape:"htmlall"}' data-object-type='{$info.object_type|escape:'quotes'}'/>{/if}
{else}
  {$noValueTxt|escape:"htmlall"}
{/if}
