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
    {$LSsession_css}
  </head>
<body>

{include file='ls:LSdefault.tpl'}

{$LSsession_js}

<div id="fatal_error">
	<h1>{tr msg="A fatal error occured. If problem persist, please contact support."}</h1>

{if $fatal_error}
	<pre class='details'>
<em>{tr msg="Details"} :</em>
{$fatal_error}</p>
{/if}
</div>

</body>
</html>
