<ul class='LSform{if $multiple && !$freeze} LSformElement_multiple'{/if}' id='{$attr_name}'>
  {if $parseValues}
    {foreach from=$parseValues item=parseValue}
      {if $parseValue.translated}{assign var=value value=$parseValue.translated}{else}{assign var=value value=$parseValue.value}{/if}
      {if $parseValue.label!="no"}{assign var=label value=$parseValue.label}{else}{assign var=label value=""}{/if}
      <li>{include file=$fieldTemplate}</li>
    {foreachelse}
      {assign var=value value=""}
      <li>{include file=$fieldTemplate}</li>
    {/foreach}
  {else}
    {foreach from=$values item=value}
      <li>{include file=$fieldTemplate}</li>
    {foreachelse}
      {assign var=value value=""} 
      <li>{include file=$fieldTemplate}</li>
    {/foreach}
  {/if}
</ul>
