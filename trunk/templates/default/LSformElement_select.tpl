{if $freeze}
  <ul class='LSform'>
    {foreach from=$values item=value}
      <li>{$possible_values.$value}</li>
    {foreachelse}
      <li>{$noValueTxt}</li>
    {/foreach}
  </ul>
{else}
  <ul class='LSform'>
    <li>
      <select name='{$attr_name}[]' {if $multiple}multiple{/if} class='LSformElement_select'>
        {html_options options=$possible_values selected=$values}
      </select>
    </li>
  </ul>
{/if}
