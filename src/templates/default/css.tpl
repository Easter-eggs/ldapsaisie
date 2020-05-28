{if isset($LibsCssFiles) && is_array($LibsCssFiles) && !empty($LibsCssFiles)}
<!-- Additional libraries CSS files -->
{foreach $LibsCssFiles as $file}
<link rel="stylesheet" type="text/css" href="libs/{$file}" title="Normal" />
{/foreach}
{/if}

{if isset($CssFiles) && is_array($CssFiles) && !empty($CssFiles)}
<!-- Additional CSS files -->
{foreach $CssFiles as $file}
<link rel="stylesheet" type="text/css" href="{css name=$file}" title="Normal" />
{/foreach}
{/if}

{if isset($defaultCssFiles) && is_array($defaultCssFiles) && !empty($defaultCssFiles)}
<!-- Default CSS files -->
{foreach $defaultCssFiles as $file}
<link rel="stylesheet" type="text/css" href="{css name=$file}" title="Normal" />
{/foreach}
{/if}
