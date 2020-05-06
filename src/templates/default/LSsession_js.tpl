{if isset($JSscripts) && is_array($JSscripts)}
<!-- JS files -->
{foreach $JSscripts as $file}
<script src="js/{$file}" type="text/javascript"></script>
{/foreach}
{/if}

<!-- Set LSdebug status -->
<script type='text/javascript'>LSdebug_active = {if $LSdebug}1{else}0{/if};</script>

{if isset($LibsJSscripts) && is_array($LibsJSscripts)}
<!-- Additional libraries JS files -->
{foreach $LibsJSscripts as $file}
<script src="libs/{$file}" type="text/javascript"></script>
{/foreach}
{/if}
