{extends file='ls:base_connected.tpl'}
{block name="content"}
<form action='search' method='post' class='LSview_search' id='LSsearch_form'>
<div class='LSview_search'>
  <input type='text' name='pattern' class='LSview_search' value='{$pattern|escape:"htmlall"}'/>
  <input type='submit' value='{tr msg="Search"}' name='LSsearch_submit' class='LSview_search' />
</div>

<h1>{tr msg="Global search"}</h1>

{include file='ls:LSview_actions.tpl'}

{foreach from=$pages item=page}
{$page}
{foreachelse}
<p style="text-align: center">{tr msg="This search didn't get any result."}</p>
{/foreach}
</form>

{/block}
