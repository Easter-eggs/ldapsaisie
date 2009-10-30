<div class='LSobject-select' id='LSobject-select-main-div'>
  <h1 id='LSselect_title'>
    {$pagetitle}
  </h1>

  <form action='{$searchForm.action}' method='post' class='LSview_search LSselect_search btn' id='LSselect_search_form'>
    {foreach from=$searchForm.hiddenFields item=field_value key=field_name}
      <input type='hidden' name='{$field_name}' value='{$field_value}' />
    {/foreach}
    
    {if $LSsession_subDn!=""}
      <label id='LSselect_topDn_label'>{$searchForm.labels.level}
        <select name='subDn' id='LSselect_topDn'>
          {html_options values=$LSsession_subDn_indexes output=$LSsession_subDn_names selected=$searchForm.values.basedn}
        </select>
      </label>
    {/if}
    <div class='LSselect_search'>
      <input type='text' name='pattern' class='LSview_search' value="{$searchForm.values.pattern}"/>
      <input type='submit' value='{$searchForm.labels.submit}' name='{$searchForm.names.submit}' class='LSview_search' />
      <img src='{$LS_IMAGES_DIR}/refresh.png' alt='{$searchForm.labels.refresh}' title='{$searchForm.labels.refresh}' id='LSselect_refresh_btn' />
      <p id='LSview_search_param'>
        <label class='LSview_search'>{$searchForm.labels.approx} : <input type='checkbox' name='approx' class='LSview_search' {if $searchForm.values.approx!=''}checked="true"{/if} /></label>
        {if $searchForm.recursive}<label class='LSview_search'>{$searchForm.labels.recursive} : <input type='checkbox' name='recursive' class='LSview_search' {if $searchForm.values.recursive!=''}checked="true"{/if}/></label>{/if}
      </p>
    </div>
  </form>
  <div id='content'>
    {include file='select_table.tpl'}
  </div>
</div>
<script type='text/javascript'>
LSselect_multiple = {$searchForm.multiple};
varLSselect = new LSselect();
</script>
