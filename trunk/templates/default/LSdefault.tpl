<div id='LSjsConfig'>
{$LSjsConfig}
</div>

<div id='LSinfos'>{$LSinfos}</div>

<div id='LSerror'>{$LSerrors}</div>

<div id='LSdebug'>
  <span id='LSdebug_hidden'>X</span> 
  <div id='LSdebug_infos'>{if $LSdebug != ''}{$LSdebug}{/if}</div>
</div>

<div id='LSlang_select'>
{foreach from=$LSlanguages item=lang}
  <img src='{$LS_IMAGES_DIR}/{$lang}.png' alt='{$lang}' title='{$lang}'/>
{/foreach}
</div>
