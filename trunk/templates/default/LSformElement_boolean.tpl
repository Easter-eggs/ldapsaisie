<ul class='LSform' id='{$attr_name}'>
  {foreach from=$values item=value}
    <li{if !$freeze} class='LSformElement_boolean'{/if}>{include file=$fieldTemplate}</li>
  {foreachelse}
    <li{if !$freeze} class='LSformElement_boolean'{/if}>{include file=$fieldTemplate}</li>
  {/foreach}
</ul>
