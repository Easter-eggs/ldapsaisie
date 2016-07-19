<!DOCTYPE html>
<html>
  <head>
    <meta charset="{$LSencoding}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle}{/if}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="icon" type="image/png" href="images/default/favicon.png" />
    <link rel="stylesheet" type="text/css" href="{css name='base.css'}" title="Normal" />
    <link rel="stylesheet" type="text/css" href="{css name='base_print.css'}" media='print' title="Normal" />
    {$LSsession_css}
  </head>
<body class="hold-transition fixed skin-blue sidebar-mini">

{include file='ls:LSdefault.tpl'}

{block "body"}{/block}

{$LSsession_js}
</body>
</html>
