<ul class='LSform{if $multiple && !$freeze} LSformElement_multiple{/if}' id='{$attr_name|escape:"quotes"}'>
  {foreach from=$values key=value_idx item=value}
    <li>{include file="ls:$fieldTemplate"}</li>
  {foreachelse}
    {assign var=value value=""}
    {assign var=value_idx value=0}
    <li>{include file="ls:$fieldTemplate"}</li>
  {/foreach}
</ul>
