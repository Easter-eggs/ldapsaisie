<div class='LSobject-select' id='LSobject-select-main-div'>
  {if count($selectable_object_types) == 1}
  <h1 id='LSselect_title'>
    {$pagetitle|escape:"htmlall"}
  </h1>
  {else}
    <ul class="LSselect_selectable_object_types">
      {foreach $selectable_object_types as $type => $label}
      <li data-object-type='{$type|escape:'quotes'}'{if $selectable_object_type==$type} class="current"{/if}>{$label}</li>
      {/foreach}
    </ul>
  {/if}

  <form action='{$searchForm.action}' method='post' class='LSview_search LSselect_search btn' id='LSselect_search_form'>
    {foreach from=$searchForm.hiddenFields item=field_value key=field_name}
      <input type='hidden' name='{$field_name|escape:"htmlall"}' value='{$field_value|escape:"htmlall"}' />
    {/foreach}

    {if $LSsession_subDn!=""}
      <label id='LSselect_topDn_label'>{tr msg="Level"|escape:"htmlall"}
        <select name='subDn' id='LSselect_topDn'>
          {html_options values=$LSsession_subDn_indexes output=$LSsession_subDn_names selected=$searchForm.values.basedn}
        </select>
      </label>
    {/if}
    <div class='LSselect_search'>
      <input type='text' name='pattern' class='LSview_search' value='{$searchForm.values.pattern|escape:"htmlall"}'/>
      <input type='submit' value='{tr msg="Search"|escape:"htmlall"}' name='{$searchForm.names.submit|escape:"htmlall"}' class='LSview_search' />
      <img src='{img name='refresh'}' alt='{tr msg="Refresh"|escape:"htmlall"}' title='{tr msg="Refresh"|escape:"htmlall"}' id='LSselect_refresh_btn' />
      <p id='LSview_search_param'>
        <label class='LSview_search'>{tr msg="Approximative search"|escape:"htmlall"} : <input type='checkbox' name='approx' class='LSview_search' {if $searchForm.values.approx!=''}checked="true"{/if} /></label>
        {if $searchForm.recursive}<label class='LSview_search'>{tr msg="Recursive search"|escape:"htmlall"} : <input type='checkbox' name='recursive' class='LSview_search' {if $searchForm.values.recursive!=''}checked="true"{/if}/></label>{/if}
      </p>
    </div>
  </form>
  <div id='content'>
    {include file='ls:select_table.tpl'}
  </div>
</div>
<script type='text/javascript'>
varLSselect = new LSselect();
</script>
