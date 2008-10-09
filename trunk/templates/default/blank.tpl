<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>LdapSaisie{if $pagetitle != ''} - {$pagetitle}{/if}</title>
    <link rel="stylesheet" type="text/css" href="{$LS_CSS_DIR}/base.css" title="Normal" />
    <link rel="stylesheet" type="text/css" href="{$LS_CSS_DIR}/base_print.css" media='print' title="Normal" />
    {$LSsession_css}
    {$LSsession_js}
  </head>
<body>
<div id='LSerror'>
{$LSerrors}
</div>
<div id='LSdebug'>
  <a href='#' id='LSdebug_hidden'>X</a> 
  <div id='LSdebug_infos'>{if $LSdebug != ''}{$LSdebug}{/if}</div>
</div>
</body>
</html>
