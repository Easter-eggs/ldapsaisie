<ul class='LSform LSformElement_select_object' id='{$attr_name}'>
  {foreach from=$values item=txt key=dn}
    <li>{include file=$fieldTemplate}</li>
  {foreachelse}
    <li>{include file=$fieldTemplate}</li>
  {/foreach}
</ul>
