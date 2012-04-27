<dl class='LSform LSmail'>
  <dt class='LSform'>{$LSmail_mail_label}</dt>
  <dd class='LSform'>
    {if $LSmail_mails != ""}
      {if $LSmail_mails|@count==1}
      <input type='text' name='LSmail_mail' id='LSmail_mail' value='{$LSmail_mails[0]}'/>
      {else}
      <select name='LSmail_mail' id='LSmail_mail'>
        {html_options values=$LSmail_mails output=$LSmail_mails}
      </select>
      {/if}
    {else}
      <input type='text' name='LSmail_mail' id='LSmail_mail'/>
    {/if}
  </dd>
  <dt class='LSform'>{$LSmail_subject_label}</dt>
  <dd class='LSform'>
    <input type='text' name='LSmail_subject' id='LSmail_subject' value="{$LSmail_subject}"/>
  </dd>
  <dt class='LSform'>{$LSmail_msg_label}</dt>
  <dd class='LSform'>
    <textarea name='LSmail_msg' id='LSmail_msg'>{$LSmail_msg}</textarea>
  </dd>
</dl>
