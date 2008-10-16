{if $freeze}
  {if $value.type}
    <span class='LSformElement_ssh_key_short_display' title='{$span_title}'>{$value.shortTxt}...</span> (Type : {$value.type}) <a href='mailto:{$value.mail}'>{$value.mail}</a><p class='LSformElement_ssh_key_value'>{$value.value}</p>
  {else}
    <span class='LSformElement_ssh_key_short_display'>{$value.shortTxt}...</span> ({$unknowTypeTxt})<p class='LSformElement_ssh_key_value'>{$value.value}</p>
  {/if}
{else}
  <textarea name='{$attr_name}[]' class='LSform LSformElement_ssh_key'>{$value}</textarea>
{/if}
