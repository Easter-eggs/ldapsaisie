<ul class='LSform{if $multiple && !$freeze} LSformElement_multiple'{/if}' id='{$attr_name}'>
  {foreach from=$values item=value}
    <li>{include file=$fieldTemplate}</li>
  {foreachelse}
    {assign var=value value=""} 
    <li>{include file=$fieldTemplate}</li>
  {/foreach}
</ul>
