{extends file='ls:base_connected.tpl'}
{block name="content"}
    {if $pagetitle != ''}<h1 id='LSview_title'>{$pagetitle|escape:"htmlall"}</h1>{/if}

<div class='LSform'>
<form action='object/{$LSobject|escape:"url"}/export' method='get'>
<dl class='LSform'>
  <dt class='LSform'><label for='ioFormat'>{tr msg='Format'}</label></dt>
  <dd class='LSform'>
    <select name='ioFormat'>
      {html_options options=$ioFormats}
    </select>
  </dd>

  <dd class='LSform'><input type='submit' value='{tr msg='Valid'}'/></dd>
</dl>
</form>
</div>
{/block}
