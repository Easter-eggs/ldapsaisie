{if $freeze}
  {if $value or $parseValue}
    {if $parseValue}
      <span class='LSformElement_supannRoleEntite'>{if $label_role}<img src='{$LS_IMAGES_DIR}/LSformElement_supannRoleEntite_label_{$label_role}.png' alt='[{$label_role}]' title='{$label_role}' /> {/if}<span title='{$parseValue.role.value}'>{$role}</span> : {if $label_code}<img src='{$LS_IMAGES_DIR}/LSformElement_supannRoleEntite_label_{$label_code}.png' alt='[{$label_code}]' title='{$label_code}' /> {/if}<span title='{$parseValue.code.value}'>{$code}</span> ({if $label_type}<img src='{$LS_IMAGES_DIR}/LSformElement_supannRoleEntite_label_{$label_type}.png' alt='[{$label_type}]' title='{$label_type}' /> {/if}<span title='{$parseValue.type.value}'>{$type}</span>)</span> 
    {else}
      <span class='LSformElement_supannRoleEntite_unparsed'>{$value}</span>
    {/if}
  {else}
    {$noValueTxt}
  {/if}
{else}
<input type='text' name='{$attr_name}[]' class='LSformElement_text' value="{$value}" autocomplete="off"/>
{/if}