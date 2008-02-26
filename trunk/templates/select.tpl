<div class='LSobject-select'>
  <form action='{$LSview_search.action}' method='post' class='LSview_search LSselect_search'>
    <input type='hidden' name='LSobject' value='{$LSview_search.LSobject}' />
    <input type='text' name='LSview_pattern' class='LSview_search' />
    <input type='submit' value='{$LSview_search.submit}' class='LSview_search' />
    <label class='LSview_search'>Recherche approximative : <input type='checkbox' name='LSview_approx' class='LSview_search' /></label>
  </form>
  <h1 id='title'>
    {$pagetitle}
  </h1>
  <div id='content'>
    {include file='select_table.tpl'}
  </div>
</div>
<script type='text/javascript'>
varLSselect = new LSselect();
</script>
