{extends file="ls:empty.tpl"}
{block "content"}
  {if $pagetitle != '' || !empty($listAvailableDataEntryForm)} 
  <section class="content-header">
    <h1>{$pagetitle}</h1>

    {if !empty($listAvailableDataEntryForm)}
    <p class='pull-right LSform_listAvailableDataEntryForm'>
      <label>
        {$DataEntryFormLabel}
        <select id='LSform_listAvailableDataEntryForm'>
	  <option value=''>--</option>
          {html_options options=$listAvailableDataEntryForm selected=$LSform_dataEntryForm}
        </select>
      </label>
    </p>
    {/if}

  </section>
  {/if}
  <section class="content">

    {include file='ls:LSform.tpl'}

  </section>
{/block}
