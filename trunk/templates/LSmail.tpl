<dl class='LSform'>
  <dt class='LSform'>{$LSmail_mail_label}</dt>
  <dd class='LSform'>
    {if $LSmail_mails != ""}
      <select name='LSmail_mail' id='LSmail_mail'>
        {html_options values=$LSmail_mails output=$LSmail_mails}
      </select>
    {else}
      <input type='text' name='LSmail_mail' id='LSmail_mail'/>
    {/if}
  </dd>
  <dt class='LSform'>{$LSmail_subject_label}</dt>
  <dd class='LSform'>
    <input type='text' name='LSmail_subject' id='LSmail_subject'/>
  </dd>
  <dt class='LSform'>{$LSmail_msg_label}</dt>
  <dd class='LSform'>
    <textarea name='LSmail_msg' id='LSmail_msg'>{$LSmail_msg}</textarea>
  </dd>
</dl>
