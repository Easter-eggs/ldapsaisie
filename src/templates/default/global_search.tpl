{include file='ls:top.tpl'}


<form action='search' method='post' class='LSview_search' id='LSsearch_form'>
<div class='LSview_search'>
  <input type='text' name='pattern' class='LSview_search' value='{$pattern|escape:"htmlall"}'/>
  <input type='submit' value='{tr msg="Search"}' name='LSsearch_submit' class='LSview_search' />
</div>

<h1>{tr msg="Global search"}</h1>

{if $LSview_actions != ''}
<ul class='LSview-actions LSview_search'>
  {foreach from=$LSview_actions item=item}
    {if is_array($item)}
      <li class='LSview-actions'><a href='{$item.url}' class='LSview-actions'><img src='{img name=$item.action}' alt='{$item.label|escape:"htmlall"}' title='{$item.label|escape:"htmlall"}' /> {$item.label|escape:"htmlall"}</a></li>
    {/if}
  {/foreach}
</ul>
{/if}

{foreach from=$pages item=page}
{$page}
{foreachelse}
<p style="text-align: center">{tr msg="This search didn't get any result."}</p>
{/foreach}
</form>

{include file='ls:bottom.tpl'}
