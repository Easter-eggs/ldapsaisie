<div class='LSobject-select' id='LSobject-select-main-div'>
  <h1 id='title'>
    {$pagetitle}
  </h1>

  <form action='{$LSview_search.action}' method='post' class='LSview_search LSselect_search btn' id='LSselect_search_form'>
    {foreach from=$LSview_search_hidden_fields item=field_value key=field_name}
      <input type='hidden' name='{$field_name}' value='{$field_value}' />
    {/foreach}
    
    {if $LSsession_subDn!=""}
      <label id='LSselect_topDn_label'>{$label_level}
        <select name='LSselect_topDn' id='LSselect_topDn'>
          {html_options values=$LSsession_subDn_indexes output=$LSsession_subDn_names selected=$LSselect_topDn}
        </select>
      </label>
    {/if}
    <div class='LSselect_search'>
      <input type='text' name='LSview_pattern' class='LSview_search' value="{$LSview_search_pattern}"/>
      <input type='submit' value='{$LSview_search.submit}' name='LSview_search_submit' class='LSview_search' />
      <img src='templates/images/refresh.png' alt='{$_refresh}' title='{$_refresh}' id='LSselect_refresh_btn' />
      <p id='LSview_search_param'>
        <label class='LSview_search'>{$LSview_search_approx_label} : <input type='checkbox' name='LSview_approx' class='LSview_search' {if $LSview_search_approx!=''}checked{/if} /></label>
        <label class='LSview_search'>{$LSview_search_recur_label} : <input type='checkbox' name='LSview_recur' class='LSview_search' {if $LSview_search_recur!=''}checked{/if}/></label>
      </p>
    </div>
  </form>
  <div id='content'>
    {include file='select_table.tpl'}
  </div>
</div>
<script type='text/javascript'>
varLSselect = new LSselect();
</script>
