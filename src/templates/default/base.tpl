<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset={$LSencoding}">
    <title>LdapSaisie{if isset($pagetitle) && $pagetitle} - {$pagetitle|escape:"htmlall"}{/if}</title>
    <base href="{$public_root_url}/"/>
    <link rel="icon" href="image/favicon" />
    {block name="css"}
    <link rel="stylesheet" type="text/css" href="{css name='base.css'}" title="Normal" />
    <link rel="stylesheet" type="text/css" href="{css name='base_print.css'}" media='print' title="Normal" />
    {include file='ls:css.tpl'}
    {/block}
    {block name="head"}{/block}
  </head>
<body>

{include file='ls:LSdefault.tpl'}

{block name="body"}{/block}

{include file='ls:js.tpl'}
{block name="js"}{/block}
</body>
</html>
