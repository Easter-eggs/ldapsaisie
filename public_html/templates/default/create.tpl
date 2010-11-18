{include file='top.tpl'}
    {if $pagetitle != ''}<h1 id='LSform_title'>{$pagetitle}</h1>{/if}

    {if !empty($listAvailableDataEntryForm)}
      <p class='LSform_listAvailableDataEntryForm'><label>{$DataEntryFormLabel}
      <select id='LSform_listAvailableDataEntryForm'>
	<option value=''>--</option>
        {html_options options=$listAvailableDataEntryForm selected=$LSform_dataEntryForm}
      </select>
      </label>
    {/if}
    
    {include file='LSform.tpl'}
{include file='bottom.tpl'}
