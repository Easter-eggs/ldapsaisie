<ul class='LSform' id='{$attr_name}'>
{if $freeze}
  {foreach from=$values item=value}
    <li>{$possible_values.$value}</li>
  {foreachelse}
    <li>{$noValueTxt}</li>
  {/foreach}
{else}
  <li>
    <select name='{$attr_name}[]' {if $multiple}multiple{/if} class='LSformElement_select'>
      {html_options options=$possible_values selected=$values}
    </select>
  </li>
{/if}
</ul>
