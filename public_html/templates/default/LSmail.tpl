<dl class='LSform{if $LSmail_options.class} {$LSmail_options.class|escape:"htmlall"}{/if}'>
  {if $LSmail_options.display_mail_field}
  <dt class='LSform'>{$LSmail_mail_label|escape:"htmlall"}</dt>
  <dd class='LSform'>
    {if $LSmail_mails != ""}
      {if $LSmail_mails|@count==1}
      <input type='text' name='LSmail_mail' id='LSmail_mail' value='{$LSmail_mails[0]|escape:"htmlall"}'/>
      {else}
      <select name='LSmail_mail' id='LSmail_mail'>
        {html_options values=$LSmail_mails output=$LSmail_mails}
      </select>
      {/if}
    {else}
      <input type='text' name='LSmail_mail' id='LSmail_mail'/>
    {/if}
  </dd>
  {else}
    <input type='hidden' name='LSmail_mail' id='LSmail_mail' value='{$LSmail_mails[0]|escape:"htmlall"}'/>
  {/if}
  {if $LSmail_options.display_subject_field}
  <dt class='LSform'>{$LSmail_subject_label|escape:"htmlall"}</dt>
  <dd class='LSform'>
    <input type='text' name='LSmail_subject' id='LSmail_subject' value='{$LSmail_subject|escape:"htmlall"}'/>
  </dd>
  {else}
    <input type='hidden' name='LSmail_subject' id='LSmail_subject' value='{$LSmail_subject|escape:"htmlall"}'/>
  {/if}
  <dt class='LSform'>{$LSmail_msg_label|escape:"htmlall"}</dt>
  <dd class='LSform'>
    <textarea name='LSmail_msg' id='LSmail_msg'>{$LSmail_msg|escape:"htmlall"}</textarea>
  </dd>
</dl>
