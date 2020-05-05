<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset={$LSencoding}">
    <title>LdapSaisie - {if isset($pagetitle) && $pagetitle}{$pagetitle|escape:"htmlall"}{else}{$error}{/if}</title>
    <base href="{$public_root_url}/"/>
    <link rel="icon" href="image/favicon" />
    <link rel="stylesheet" type="text/css" href="{css name='base.css'}" title="Normal" />
    <link rel="stylesheet" type="text/css" href="{css name='base_print.css'}" media='print' title="Normal" />
    {include file='ls:LSsession_css.tpl'}
  </head>
<body>

{include file='ls:LSdefault.tpl'}

<div id="error">
	<h1>{$error}</h1>

{if isset($details)}
	<pre class='details'>
<em>{tr msg="Details"} :</em>
{$details}</p>
{/if}
</div>

{include file='ls:LSsession_js.tpl'}
</body>
</html>
