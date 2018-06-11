{if $freeze}
  {if $value}
    {if $quotas[$value].unknown}
      <span class='LSformElement_quota_unknown'>{$quotas[$value].unknown|escape:"htmlall"}</span>
    {else}
      {$quotas[$value].valueTxt|escape:"htmlall"}
    {/if}
  {else}
    {$noValueTxt|escape:"htmlall"}
  {/if}
{else}
  <input name='{$attr_name|escape:"quotes"}_size[]' type=text class='LSformElement_quota' value='{$quotas[$value].valueSize|escape:"quotes"}'/>
  <select name='{$attr_name|escape:"quotes"}_sizeFact[]' class='LSform LSformElement_quota'>
    {html_options options=$sizeFacts selected=$quotas[$value].valueSizeFact}
  </select>
  {if $quotas[$value].unknown}
    <span class='LSformElement_quota_unknown'>{$quotas[$value].unknown|escape:"htmlall"}</span>
  {/if}
{/if}
