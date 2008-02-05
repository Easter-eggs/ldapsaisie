<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>LdapSaisie{if $pagetitle != ''} - {$pagetitle}{/if}</title>
		<link rel="stylesheet" type="text/css" href="templates/css/base.css" media="screen" title="Normal" />
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

<div id='main'>
	<div id='left'>
		<img src='templates/images/logo.png' alt='Logo' id='logo'/>
		<ul class='menu'>
			<li class='menu'><a href='index.php' class='menu'>Mon compte</a></li>
			<li class='menu'><a href='mon_compte.php' class='menu'>Utilisateurs</a></li>
			<li class='menu'><a href='mon_compte.php' class='menu'>Groupes</a></li>
		</ul>
	</div>
	<div id='right'>
		<p id='status'>Connecté en tant que <span id='user_name'>{$LSsession_username}</span></b> <a href='index.php?LSsession_logout'><img src='templates/images/logout.png' alt='Logout' title='Logout' /></a></p>
		<h1>Mon compte</h1>
		<form action='{$LSform_action}' method='post' class='LSform'>
		{$LSform_header}
		<dl class='LSform'>
			{foreach from=$LSform_fields item=field}
			<dt class='LSform'>{$field.label}</dt>
			<dd class='LSform'>{$field.html}{if $field.add != ''} <span class='LSform-addfield'>+ Ajouter un champ</span>{/if}</dd>
			{if $field.errors != ''}
				{foreach from=$field.errors item=error}
				<dd class='LSform LSform-errors'>{$error}</dd>
				{/foreach}
			{/if}
			{/foreach}
		</dl>
		<input type='submit' value='{$LSform_submittxt}' class='LSform' />
	</div>
	<hr class='spacer' />
</div>
</body>
</html>
