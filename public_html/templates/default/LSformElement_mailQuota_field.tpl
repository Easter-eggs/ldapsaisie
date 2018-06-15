{if $freeze}
  {if $value}
    {if $quotas[$value].unknown}
      <span class='LSformElement_mailQuota_unknown'>Valeur incorrecte</span>
    {else}
      {$quotas[$value].valueTxt|escape:"htmlall"}
    {/if}
  {else}
    {$noValueTxt|escape:"htmlall"}
  {/if}
{else}
  <input name='{$attr_name|escape:"htmlall"}_size[]' type=text class='LSformElement_mailQuota' value='{$quotas[$value].valueSize|escape:"htmlall"}'/>
  <select name='{$attr_name|escape:"htmlall"}_sizeFact[]' class='LSform LSformElement_mailQuota'>
    {html_options options=$sizeFacts selected=$quotas[$value].valueSizeFact}
  </select>
  {if $quotas[$value].unknown}
    <span class='LSformElement_mailQuota_unknown'>{tr msg="Incorrect value"}</span>
  {/if}
{/if}
