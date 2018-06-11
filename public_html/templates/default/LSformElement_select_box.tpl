<ul class='LSform' id='{$attr_name|escape:"quotes"}'>
{if $freeze}
  {foreach from=$values item=value}
    {LSformElement_select_checkIsValidValue value=$value possible_values=$possible_values}
    {if $LSformElement_select_isValidValue}
    <li>{$LSformElement_select_isValidValue_label|escape:"htmlall"}</li>
    {else}
    <li class='LSform-errors'>{getFData format=$unrecognized_value_label_format data=$value}</li>
    {/if}
  {foreachelse}
    <li>{$noValueTxt|escape:"htmlall"}</li>
  {/foreach}
{else}
    {foreach from=$possible_values item=label key=value name=LSformElement_selectbox}
      {if is_array($label)}
        {if count($label.possible_values)>0}
        <li>
          <span class='LSformElement_selectbox_sub_values_label'>{$label.label|escape:"htmlall"} :</span>
          <ul class='LSformElement_selectbox_sub_values'>
            {foreach from=$label.possible_values item=l key=v name=LSformElement_selectbox_sub_values}
              <li>
                <input type='{if $multiple}checkbox{else}radio{/if}' name='{$attr_name|escape:"quotes"}[]' class='LSformElement_selectbox' id='LSformElement_selectbox_{$attr_name|escape:"quotes"}_{$smarty.foreach.LSformElement_selectbox.index}_{$smarty.foreach.LSformElement_selectbox_sub_values.index}' value='{$v|escape:"quotes"}' {if in_array($v,$values)}checked{/if}/> <label for='LSformElement_selectbox_{$attr_name|escape:"quotes"}_{$smarty.foreach.LSformElement_selectbox.index}_{$smarty.foreach.LSformElement_selectbox_sub_values.index}'>{tr msg=$l}</label>
              </li>
            {/foreach}
          </ul>
        </li>
        {/if}
      {else}
        <li>
          <input type='{if $multiple}checkbox{else}radio{/if}' name='{$attr_name|escape:"quotes"}[]' class='LSformElement_selectbox' id='LSformElement_selectbox_{$attr_name|escape:"quotes"}_{$smarty.foreach.LSformElement_selectbox.index}' value='{$value|escape:"quotes"}' {if in_array($value,$values)}checked{/if}/> <label for='LSformElement_selectbox_{$attr_name|escape:"quotes"}_{$smarty.foreach.LSformElement_selectbox.index}'>{tr msg=$label}</label>
        </li>
      {/if}
    {/foreach}
{/if}
</ul>
