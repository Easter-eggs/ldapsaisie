<div id='LSjsConfig'>{$LSjsConfig}</div>

<div id='LSinfos'>{$LSinfos}</div>

<div id='LSerror'>{$LSerrors}</div>

<div id='LSdebug'>{$LSdebug_content}</div>

<div id="_smarty_console"></div>

<div id='LSlang_select'>
{foreach from=$LSlanguages item=lang}
  <img src='{img name=$lang}' alt='{$lang}' title='{$lang}'/>
{/foreach}
</div>
