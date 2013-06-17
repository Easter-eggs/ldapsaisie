<ul class='LSform{if $multiple && !$freeze} LSformElement_multiple'{/if}' id='{$attr_name}'>
  {foreach from=$values_txt item=value}
    <li>{include file="ls:$fieldTemplate"}</li>
  {foreachelse}
    <li>{include file="ls:$fieldTemplate"}</li>
  {/foreach}
</ul>
