{include file='top.tpl'}
    {if $pagetitle != ''}<h1>{$pagetitle}</h1>{/if}
    
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
      <dd class='LSform'><input type='submit' value='{$LSform_submittxt}' class='LSform' /></dd>
    </dl>
    </form>
{include file='bottom.tpl'}
