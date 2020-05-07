{if isset($LSsession_username) && $LSsession_username}
{assign var="extended_tpl" value="ls:base_connected.tpl"}
{assign var="extended_block" value="content"}
{else}
{assign var="extended_tpl" value="ls:base.tpl"}
{assign var="extended_block" value="body"}
{/if}
{extends file=$extended_tpl}
{block name=$extended_block}

<div id="error">
	<h1>{$error}</h1>

{if isset($details)}
	<pre class='details'>
<em>{tr msg="Details"} :</em>
{$details}</p>
{/if}
</div>

{/block}
