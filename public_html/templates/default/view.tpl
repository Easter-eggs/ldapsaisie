{extends file="ls:empty.tpl"}
{block "content"}
  {if $pagetitle != '' || $LSview_actions != ''}
  <section class="content-header">
    <h1>{$pagetitle}</h1>

    {if $LSview_actions != ''}
    <div class="pull-right">
    <ul class="nav nav-pills pull-right">
      {foreach from=$LSview_actions item=item}
        {if is_array($item)}
        <li role="presentation"><a href="{$item.url}" class="{if $item.class} {$item.class}{/if}" ><img src="{img name=$item.action}" alt="{$item.label}" title="{$item.label}" />{if !isset($item.hideLabel) || !$item.hideLabel} {$item.label}{/if}</a></li>
        {/if}
      {/foreach}
    </ul>
    </div>
    {/if}

  </section>
  {/if}
  <section class="content">

    {include file='ls:LSform_view.tpl'}

    {if $LSrelations!=''}
      {foreach from=$LSrelations item=item}
        {include file='ls:LSrelations.tpl'}
      {/foreach}
    {/if}

  </section>
{/block}
