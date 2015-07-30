{include file='ls:top.tpl'}
    {if $pagetitle != ''}<h1 id='LSview_title'>{$pagetitle}</h1>{/if}

<div class='LSform'>
<form action='import.php?LSobject={$LSobject}' method='post' enctype="multipart/form-data">
<input type='hidden' name='validate' value='LSimport'/>
<dl class='LSform'>
  <dt class='LSform'><label for='importfile'>{tr msg='File'}</label></dt>
  <dd class='LSform'><input type='file' name='importfile'/></dd>

  <dt class='LSform'><label for='ioFormat'>{tr msg='Format'}</label></dt>
  <dd class='LSform'><select name='ioFormat'>{html_options options=$ioFormats}</select></dd>

  <dt class='LSform'><label for='justTry'>{tr msg='Update objects if exists'}</label></dt>
  <dd class='LSform'><input type='radio' name='updateIfExists' value='yes'/>{tr msg='yes'} <input type='radio' name='updateIfExists' value='no' checked/>{tr msg='no'}</select></dd>

  <dt class='LSform'><label for='justTry'>{tr msg='Only validate data'}</label></dt>
  <dd class='LSform'><input type='radio' name='justTry' value='yes'/>{tr msg='yes'} <input type='radio' name='justTry' value='no' checked/>{tr msg='no'}</select></dd>

  <dd class='LSform'><input type='submit' value='{tr msg='Valid'}'/></dd>
</dl>
</form>
</div>
{if is_array($result)}
<h1>{tr msg='Result'}</h1>
{if !empty($result.errors)}
<h2>{tr msg='Errors'}</h2>
{foreach $result.errors as $error}
<h3 class='LSimport'>Object {$error@iteration}</h3>
<div class='LSimport_error'>
{if !empty($error.errors.globals)}
  <ul class='LSimport_global_errors'>
  {foreach $error.errors.globals as $e}
    <li>{$e}</li>
  {/foreach}
  </ul>
{/if}
<ul class='LSimport_data_errors'>
{foreach $error.data as $key => $val}
  <li>
    <strong>{$key} :</strong>
    {if empty($val)}{tr msg='No value'}{else}{LSimport_implodeValues values=$val}{/if}
    {if isset($error.errors.attrs[$key])}
    <ul class='LSimport_attr_errors'>
      {foreach $error.errors.attrs.$key as $e}
      <li>{$e}</li>
      {/foreach}
    </ul>
    {/if}
  </li>
{/foreach}
{foreach $error.errors.attrs as $a => $es}
  {if !in_array($a,$error.data)}
  <li>
    <strong>{$a} :</strong>
    <ul class='LSimport_attr_errors'>
      {foreach $es as $e}
        <li>{$e}</li>
      {/foreach}
    </ul>
  </li>
  {/if}
{/foreach}
</ul>
</div>
{/foreach}
{/if}

<h2 class='LSimport_imported_objects'>{tr msg='Imported objects'} ({count($result.imported)})</h2>
<ul class='LSimport_imported_objects'>
{foreach $result.imported as $dn => $name}
  <li><a href='view.php?LSobject={$LSobject}&dn={$dn}'>{$name}</a></li>
{foreachelse}
  <li>{tr msg='No imported object'}</li>
{/foreach}
</ul>

{if !empty($result.updated)}
<h2 class='LSimport_updated_objects'>{tr msg='Updated objects'} ({count($result.updated)})</h2>
<ul class='LSimport_updated_objects'>
{foreach $result.updated as $dn => $name}
  <li><a href='view.php?LSobject={$LSobject}&dn={$dn}'>{$name}</a></li>
{/foreach}
</ul>
{/if}

{/if}
{include file='ls:bottom.tpl'}
