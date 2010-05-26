{if $freeze}
  {if $value}
    {if $quotas[$value].unknown}
      <span class='LSformElement_quota_unknown'>{$quotas[$value].unknown}</span>
    {else}
      {$quotas[$value].valueTxt}
    {/if}
  {else}
    {$noValueTxt}
  {/if}
{else}
  <input name='{$attr_name}_size[]' type=text class='LSformElement_quota' value="{$quotas[$value].valueSize}"/>
  <select name='{$attr_name}_sizeFact[]' class='LSform LSformElement_quota'>
    {html_options options=$sizeFacts selected=$quotas[$value].valueSizeFact}
  </select>
  {if $quotas[$value].unknown}
    <span class='LSformElement_quota_unknown'>{$quotas[$value].unknown}</span>
  {/if}
{/if}
