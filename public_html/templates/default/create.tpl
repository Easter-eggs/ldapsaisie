{include file='ls:top.tpl'}
    {if $pagetitle != ''}<h1 id='LSform_title'>{$pagetitle|escape:"htmlall"}</h1>{/if}

    {if !empty($listAvailableDataEntryForm)}
      <p class='LSform_listAvailableDataEntryForm'><label>{$DataEntryFormLabel|escape:"htmlall"}
      <select id='LSform_listAvailableDataEntryForm'>
	<option value=''>--</option>
        {html_options options=$listAvailableDataEntryForm selected=$LSform_dataEntryForm}
      </select>
      </label>
    {/if}
    
    {include file='ls:LSform.tpl'}
{include file='ls:bottom.tpl'}
