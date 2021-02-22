{if !isset($pagination_url)}
{assign var=pagination_url value=$request->current_url}
{/if}

{if $page.nbPages > 1}
  <p class='LSobject-list-page'>

  {if $page.nbPages > 9}
    {if $page.nb > 5}
      {if $page.nb+4 > $page.nbPages}
        {assign var=start value=$page.nbPages-8}
      {else}
        {assign var=start value=$page.nb-4}
      {/if}
    {else}
      {assign var=start value=1}
    {/if}
    {if $start != 1}
    <a href='{$pagination_url}?page=1' class='LSobject-list-page'>&lt;</a>
    {/if}
    {foreach from=0|range:8 item=i}
      {if $page.nb==$start+$i}
        <strong class='LSobject-list-page'>{$page.nb}</strong>
      {else}
        <a href='{$pagination_url}?page={$start+$i}'  class='LSobject-list-page'>{$start+$i}</a>
      {/if}
    {/foreach}
    {if $start + 9 <= $page.nbPages}
    <a href='{$pagination_url}?page={$page.nbPages}' class='LSobject-list-page'>&gt;</a>
    {/if}
  {else}
    {section name=listpage loop=$page.nbPages step=1}
      {if $page.nb == $smarty.section.listpage.index+1}
        <strong class='LSobject-list-page'>{$page.nb}</strong>
      {else}
        <a href='{$pagination_url}?page={$smarty.section.listpage.index+1}'  class='LSobject-list-page'>{$smarty.section.listpage.index+1}</a>
      {/if}
    {/section}
  {/if}
  </p>
{/if}
