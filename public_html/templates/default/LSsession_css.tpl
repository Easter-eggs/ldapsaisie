{if isset($CssFiles) && is_array($CssFiles)}
<!-- Additional CSS files -->
{foreach $CssFiles as $file}
<link rel="stylesheet" type="text/css" href="{css name=$file}" title="Normal" />
{/foreach}
{/if}

{if isset($LibsCssFiles) && is_array($LibsCssFiles)}
<!-- Additional libraries CSS files -->
{foreach $LibsCssFiles as $file}
<link rel="stylesheet" type="text/css" href="libs/{$file}" title="Normal" />
{/foreach}
{/if}
