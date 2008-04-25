<div class='LSobject-select'>
  <h1 id='title'>
    {$pagetitle}
  </h1>
  <form action='{$LSview_search.action}' method='post' class='LSview_search LSselect_search'>
    <input type='hidden' name='LSobject' value='{$LSview_search.LSobject}' />
    
    {if $LSsession_topDn!=""}
      <label id='LSselect_topDn_label'>{$label_level}
        <select name='LSselect_topDn' id='LSselect_topDn'>
          {html_options values=$LSsession_topDn_index output=$LSsession_topDn_name selected=$LSsession_topDn}
        </select>
      </label>
    {/if}
    <div class='LSselect_search'>
      <input type='text' name='LSview_pattern' class='LSview_search' />
      <input type='submit' value='{$LSview_search.submit}' class='LSview_search' />
      <label class='LSview_search'>Recherche approximative : <input type='checkbox' name='LSview_approx' class='LSview_search' /></label>
    </div>
  </form>
  <div id='content'>
    {include file='select_table.tpl'}
  </div>
</div>
<script type='text/javascript'>
varLSselect = new LSselect();
</script>
