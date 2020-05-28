<dl class='LSmail{if $LSmail_options.class} {$LSmail_options.class|escape:"htmlall"}{/if}'>
  {if $LSmail_options.display_mail_field}
  <dt class='LSmail'>{tr msg="Email"|escape:"htmlall"}</dt>
  <dd class='LSmail'>
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
  <dt class='LSmail'>{tr msg="Subject"|escape:"htmlall"}</dt>
  <dd class='LSmail'>
    <input type='text' name='LSmail_subject' id='LSmail_subject' value='{$LSmail_subject|escape:"htmlall"}'/>
  </dd>
  {else}
    <input type='hidden' name='LSmail_subject' id='LSmail_subject' value='{$LSmail_subject|escape:"htmlall"}'/>
  {/if}
  <dt class='LSmail'>{tr msg="Message"|escape:"htmlall"}</dt>
  <dd class='LSmail'>
    <textarea name='LSmail_msg' id='LSmail_msg'>{$LSmail_msg|escape:"htmlall"}</textarea>
  </dd>
</dl>
