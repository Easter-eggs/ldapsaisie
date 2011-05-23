{if $freeze}
  <span class='LSformElement_text'>
  {if $value}
    <a class='{$uriClass}' href='{$uriPrefix}{$value}'{if $uriTarget} target='{$uriTarget}'{/if}>{$value}</a>
  {else}
    {$noValueTxt}
  {/if}
  </span>
  <input type='hidden' name='{$attr_name}[]' class='LSformElement_text' value="{$value}"/>
{else}
  <input type='text' name='{$attr_name}[]' class='LSformElement_text {$uriClass}' value="{$value}" autocomplete="off"/>
{/if}
