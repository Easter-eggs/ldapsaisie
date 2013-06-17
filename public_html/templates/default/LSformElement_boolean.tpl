<ul class='LSform' id='{$attr_name}'>
  {foreach from=$values item=value}
    <li{if !$freeze} class='LSformElement_boolean'{/if}>{include file="ls:$fieldTemplate"}</li>
  {foreachelse}
    {assign var=value value=""}
    <li{if !$freeze} class='LSformElement_boolean'{/if}>{include file="ls:$fieldTemplate"}</li>
  {/foreach}
</ul>
