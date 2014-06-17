<ul class='LSform' id='{$attr_name}'>
{if $freeze}
  {foreach from=$values item=value}
    {if array_key_exists($value,$possible_values)}
    <li>{$possible_values.$value}</li>
    {else}
    <li class='LSform-errors'>{getFData format=$unrecognized_value_label_format data=$value}</li>
    {/if}
  {foreachelse}
    <li>{$noValueTxt}</li>
  {/foreach}
{else}
    {foreach from=$possible_values item=label key=value name=LSformElement_selectbox}
      <li>
        <input type='{if $multiple}checkbox{else}radio{/if}' name='{$attr_name}[]' class='LSformElement_selectbox' id='LSformElement_selectbox_{$attr_name}_{$smarty.foreach.LSformElement_selectbox.index}' value="{$value}" {if in_array($value,$values)}checked{/if}/> <label for='LSformElement_selectbox_{$attr_name}_{$smarty.foreach.LSformElement_selectbox.index}'>{tr msg=$label}</label>
      </li>
    {/foreach}
{/if}
</ul>
