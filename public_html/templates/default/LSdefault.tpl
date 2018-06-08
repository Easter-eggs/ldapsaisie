<div id='LSjsConfig'>{$LSjsConfig}</div>

<div id='LSinfos_txt'>{if is_array($LSinfos) && !empty($LSinfos)}
<ul>
{foreach $LSinfos as $info}
<li>{$info|escape:"htmlall"}</li>
{/foreach}
</ul>
{/if}</div>

<div id='LSerror_txt'>{$LSerrors}</div>

<div id='LSdebug_txt'>{if $LSdebug != ''}{$LSdebug}{/if}</div>

<div id="_smarty_console"></div>

<div id='LSlang_select'>
{foreach from=$LSlanguages item=lang}
  <img src='{img name=$lang}' alt='{$lang}' title='{$lang}'/>
{/foreach}
</div>
