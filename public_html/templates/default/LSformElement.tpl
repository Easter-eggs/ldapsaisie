<ul class='LSform{if $multiple && !$freeze} LSformElement_multiple'{/if}' id='{$attr_name|escape:"quotes"}'>
  {foreach from=$values item=value}
    <li>{include file="ls:$fieldTemplate"}</li>
  {foreachelse}
    {assign var=value value=""} 
    <li>{include file="ls:$fieldTemplate"}</li>
  {/foreach}
</ul>
