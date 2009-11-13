{if $freeze}
  {if $value}{$value}{else}{$noValueTxt}{/if}
{else}
  <input type='text' name='{$attr_name}[]' value="{$value}" class='LSformElement_date'>
{/if}
