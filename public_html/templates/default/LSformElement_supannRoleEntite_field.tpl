{if $freeze}
  {if $value or $parseValue}
    {if $parseValue}
      <span class='LSformElement_supannRoleEntite'>{if $label_role}<img src='{img name="LSformElement_supannRoleEntite_label_$label_role}' alt='[{$label_role}]' title='{$label_role}' /> {/if}<span title='{$parseValue.role.value}'>{$role}</span> : {if $label_code}<img src='{img name="LSformElement_supannRoleEntite_label_$label_code"}' alt='[{$label_code}]' title='{$label_code}' /> {/if}<span title='{$parseValue.code.value}'>{$code}</span> ({if $label_type}<img src='{img name="LSformElement_supannRoleEntite_label_$label_type"}' alt='[{$label_type}]' title='{$label_type}' /> {/if}<span title='{$parseValue.type.value}'>{$type}</span>)</span> 
    {else}
      <span class='LSformElement_supannRoleEntite_unparsed'>{$value}</span>
    {/if}
  {else}
    {$noValueTxt}
  {/if}
{else}
{if $parseValue}
  <input type='text' name='{$attr_name}[]' class='LSformElement_text' value="{$parseValue.value}" autocomplete="off"/>
{else}
  <input type='text' name='{$attr_name}[]' class='LSformElement_text' value="{$value}" autocomplete="off"/>
{/if}
{/if}
