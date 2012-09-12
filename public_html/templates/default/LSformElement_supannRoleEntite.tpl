<ul class='LSform{if $multiple && !$freeze} LSformElement_multiple'{/if}' id='{$attr_name}'>
  {if $parseValues}
    {foreach from=$parseValues item=parseValue}
      {if $parseValue.role.translated}{assign var=role value=$parseValue.role.translated}{else}{assign var=role value=$parseValue.role.value}{/if}
      {if $parseValue.role.label!="no"}{assign var=label_role value=$parseValue.role.label}{else}{assign var=label_role value=""}{/if}
      {if $parseValue.type.translated}{assign var=type value=$parseValue.type.translated}{else}{assign var=type value=$parseValue.type.value}{/if}
      {if $parseValue.type.label!="no"}{assign var=label_type value=$parseValue.type.label}{else}{assign var=label_type value=""}{/if}
      {if $parseValue.code.translated}{assign var=code value=$parseValue.code.translated}{else}{assign var=code value=$parseValue.code.value}{/if}
      {if $parseValue.code.label!="no"}{assign var=label_code value=$parseValue.code.label}{else}{assign var=label_code value=""}{/if}
      <li>{include file=$fieldTemplate}</li>
    {foreachelse}
      {assign var=value value=""}
      {assign var=parseValue value=""} 
      <li>{include file=$fieldTemplate}</li>
    {/foreach}
  {else}
    {foreach from=$values item=value}
      <li>{include file=$fieldTemplate}</li>
    {foreachelse}
      {assign var=value value=""} 
      {assign var=parseValue value=""} 
      <li>{include file=$fieldTemplate}</li>
    {/foreach}
  {/if}
</ul>
