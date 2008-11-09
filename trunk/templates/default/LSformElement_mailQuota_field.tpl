{if $freeze}
  {if $value}
    {if $quotas[$value].unknown}
      <span class='LSformElement_mailQuota_unknown'>Valeur incorrecte</span>
    {else}
      {$quotas[$value].valueTxt}
    {/if}
  {else}
    {$noValueTxt}
  {/if}
{else}
  <input name='{$attr_name}_size[]' type=text class='LSformElement_mailQuota' value="{$quotas[$value].valueSize}"/>
  <select name='{$attr_name}_sizeFact[]' class='LSform LSformElement_mailQuota'>
    {html_options options=$sizeFacts selected=$quotas[$value].valueSizeFact}
  </select>
  {if $quotas[$value].unknown}
    <span class='LSformElement_mailQuota_unknown'>Valeur incorrecte</span>
  {/if}
{/if}
