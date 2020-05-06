<div id='LSjsConfig'>{$LSjsConfig}</div>

<div id='LSinfos_txt'>{if isset($LSinfos) && is_array($LSinfos) && !empty($LSinfos)}
<ul>
{foreach $LSinfos as $info}
<li>{$info|escape:"htmlall"}</li>
{/foreach}
</ul>
{/if}</div>

<div id='LSerror_txt'>{if isset($LSerrors) && $LSerrors}{$LSerrors}{/if}</div>

<div id='LSdebug_txt'>{if isset($LSdebug) && $LSdebug}{$LSdebug}{/if}</div>

<div id="_smarty_console"></div>

<div id='LSlang_select'>
{foreach from=$LSlanguages item=lang}
  <img src='{img name=$lang}' alt='{$lang}' title='{$lang}'/>
{/foreach}
</div>
