<div id='LSjsConfig'>
{$LSjsConfig}
</div>

<div id='LSinfos_txt'>{$LSinfos}</div>

<div id='LSerror_txt'>{$LSerrors}</div>

<div id='LSdebug_txt'>{if $LSdebug != ''}{$LSdebug}{/if}</div>

<div id='LSlang_select'>
{foreach from=$LSlanguages item=lang}
  <img src='{$LS_IMAGES_DIR}/{$lang}.png' alt='{$lang}' title='{$lang}'/>
{/foreach}
</div>