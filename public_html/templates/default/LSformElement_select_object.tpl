{if !$freeze}
<table class='LSformElement_select_object_searchAdd'>
  <tr class='LSformElement_select_object_searchAdd'>
    <td class='LSformElement_select_object_searchAdd'>
{/if}
      <ul class='LSform LSformElement_select_object' id='{$attr_name}'>
        {foreach from=$values item=txt key=dn}
          <li>{include file=$fieldTemplate}</li>
        {foreachelse}
          {assign var=dn value=""} 
          {assign var=txt value=""} 
          <li>{include file=$fieldTemplate}</li>
        {/foreach}
      </ul>
{if !$freeze}
    </td>
    <td class='LSformElement_select_object_searchAdd'></td>
  </tr>
</table>
{/if}
