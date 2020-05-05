<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset={$LSencoding}">
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle|escape:"htmlall"}{/if}</title>
    <base href="{$public_root_url}/"/>
    <link rel="icon" type="image/png" href="images/default/favicon.png" />
    <link rel="stylesheet" type="text/css" href="{css name='base.css'}" title="Normal" />
    <link rel="stylesheet" type="text/css" href="{css name='base_print.css'}" media='print' title="Normal" />
    {include file='ls:LSsession_css.tpl'}
  </head>
<body>

{include file='ls:LSdefault.tpl'}

<div id="fatal_error">
	<h1>{tr msg="The requested page was not found."}</h1>
</div>

{include file='ls:LSsession_js.tpl'}
</body>
</html>
