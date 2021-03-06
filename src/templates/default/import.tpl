{extends file='ls:base_connected.tpl'}
{block name="content"}
    {if $pagetitle != ''}<h1 id='LSview_title'>{$pagetitle|escape:"htmlall"}</h1>{/if}

<div class='LSform'>
<form action='object/{$LSobject|escape:"url"}/import' method='post' enctype="multipart/form-data">
<input type='hidden' name='LSobject' value='{$LSobject}'/>
<input type='hidden' name='validate' value='import'/>
<dl class='LSform'>
  <dt class='LSform'><label for='importfile'>{tr msg='File'}</label></dt>
  <dd class='LSform'><input type='file' name='importfile'/></dd>

  <dt class='LSform'><label for='ioFormat'>{tr msg='Format'}</label></dt>
  <dd class='LSform'>
    <select name='ioFormat'>
      {if isset($result['ioFormat'])}
        {html_options options=$ioFormats selected=$result.ioFormat}
      {else}
        {html_options options=$ioFormats}
      {/if}
    </select>
  </dd>

  <dt class='LSform'><label for='justTry'>{tr msg='Update objects if exists'}</label></dt>
  <dd class='LSform'>
    <input type='radio' name='updateIfExists' value='yes' {if isset($result['updateIfExists']) && $result['updateIfExists']}checked{/if}/>{tr msg='yes'}
    <input type='radio' name='updateIfExists' value='no'  {if !isset($result['updateIfExists']) || !$result['updateIfExists']}checked{/if}/>{tr msg='no'}
  </dd>

  <dt class='LSform'><label for='justTry'>{tr msg='Only validate data'}</label></dt>
  <dd class='LSform'>
    <input type='radio' name='justTry' value='yes' {if isset($result['justTry']) && $result['justTry']}checked{/if}/>{tr msg='yes'}
    <input type='radio' name='justTry' value='no'  {if !isset($result['justTry']) || !$result['justTry']}checked{/if}/>{tr msg='no'}
  </dd>

  <dd class='LSform'><input type='submit' value='{tr msg='Valid'}'/></dd>
</dl>
</form>
</div>
{if $result}
<h1>{tr msg='Result'}</h1>
{if !empty($result.errors)}
<h2>{tr msg='Errors'}</h2>
{foreach $result.errors as $error}
<h3 class='LSio'>Object {$error@iteration}</h3>
<div class='LSio_error'>
{if !empty($error.errors.globals)}
  <ul class='LSio_global_errors'>
  {foreach $error.errors.globals as $e}
    <li>{$e}</li>
  {/foreach}
  </ul>
{/if}
<ul class='LSio_data_errors'>
{foreach $error.data as $key => $val}
  <li>
    <strong>{$key|escape:"htmlall"} :</strong>
    {if empty($val)}{tr msg='No value'}{else}{LSio_implodeValues values=$val}{/if}
    {if isset($error.errors.attrs[$key])}
    <ul class='LSio_attr_errors'>
      {foreach $error.errors.attrs.$key as $e}
      <li>{$e|escape:"htmlall"}</li>
      {/foreach}
    </ul>
    {/if}
  </li>
{/foreach}
{foreach $error.errors.attrs as $a => $es}
  {if !in_array($a,$error.data)}
  <li>
    <strong>{$a|escape:"htmlall"} :</strong>
    <ul class='LSio_attr_errors'>
      {foreach $es as $e}
        <li>{$e|escape:"htmlall"}</li>
      {/foreach}
    </ul>
  </li>
  {/if}
{/foreach}
</ul>
</div>
{/foreach}
{/if}

<h2 class='LSio_imported_objects'>{tr msg='Imported objects'} ({count($result.imported)})</h2>
<ul class='LSio_imported_objects'>
{foreach $result.imported as $dn => $name}
  <li><a href='object/{$LSobject|escape:"url"}/{$dn|escape:"url"}'>{$name|escape:"htmlall"}</a></li>
{foreachelse}
  <li>{tr msg='No imported object'}</li>
{/foreach}
</ul>

{if !empty($result.updated)}
<h2 class='LSio_updated_objects'>{tr msg='Updated objects'} ({count($result.updated)})</h2>
<ul class='LSio_updated_objects'>
{foreach $result.updated as $dn => $name}
  <li><a href='object/{$LSobject|escape:"url"}/{$dn|escape:"url"}'>{$name|escape:"htmlall"}</a></li>
{/foreach}
</ul>
{/if}

{/if}
{/block}
