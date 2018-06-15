<form action='{$LSform_action}' method='post' enctype="multipart/form-data" class='LSform'>
{$LSform_header}
{if $LSform_layout}
  <!-- Tabs - Start Title -->
  <ul class='LSform_layout'>
  {foreach from=$LSform_layout item=tab key=tab_key}
    <li class='LSform_layout' id='LSform_layout_btn_{$tab_key|escape:"htmlall"}'><a href='#{$tab_key|escape:"htmlall"}'>{tr msg=$tab.label}</a></li>
  {/foreach}
  </ul>
  <!-- Tabs - End Title -->

  <!-- Tabs - Start Content -->
  {foreach from=$LSform_layout item=tab key=tab_key}
    <a name='{$tab_key|escape:'htmlall'}'></a>
    <h2 class='LSform_layout'>{tr msg=$tab.label}</h2>
    <div class='LSform LSform_layout' id='LSform_layout_div_{$tab_key|escape:'htmlall'}'>
      {if $LSformElement_image!='' && $tab.img==1}
      <div class='LSformElement_image{if $LSformElement_image_errors} LSformElement_image_errors{/if}'>
        {if $LSformElement_image_actions!='' && !$LSformElement_image_errors}
        <ul class='LSformElement_image_actions'>
            <li><img src='{img name="zoom"}' class='LSformElement_image_actions LSformElement_image_action_zoom' id='LSformElement_image_action_zoom_{$LSformElement_image.id|escape:'htmlall'}' /></li>
          {foreach from=$LSformElement_image_actions item=item}
            <li><img src='{img name=$item}' class='LSformElement_image_actions LSformElement_image_action_{$item|escape:'htmlall'}' id='LSformElement_image_action_{$item|escape:'htmlall'}_{$LSformElement_image.id|escape:'htmlall'}' /></li>
          {/foreach}
        </ul>
        {/if}
        <img src='{$LSformElement_image.img}' class='LSformElement_image LSsmoothbox' id='LSformElement_image_{$LSformElement_image.id|escape:'htmlall'}' />
      </div>
      {/if}
      
      <dl class='LSform'>
        {foreach from=$tab.args item=arg}
          {if $LSform_fields[$arg]}
            <dt class='LSform{if $LSform_fields[$arg].errors != ''} LSform-errors{/if}'>{$LSform_fields[$arg].label}{if $LSform_fields[$arg].required} *{/if}{if $LSform_fields[$arg].help_info!=""} <img class='LStips' src="{img name='help'}" alt='?' title='{$LSform_fields[$arg].help_info|escape:'htmlall'}'/>{/if}</dt>
            <dd class='LSform'>{$LSform_fields[$arg].html}{if $LSform_fields[$arg].add != ''} <span class='LSform-addfield'>+ Ajouter un champ</span>{/if}</dd>
            {if $LSform_fields[$arg].errors != ''}
              {foreach from=$LSform_fields[$arg].errors item=error}
              <dd class='LSform LSform-errors'>{$error|escape:'htmlall'}</dd>
              {/foreach}
            {/if}
          {/if}
        {/foreach}
        <dd class='LSform'><input type='submit' value='{$LSform_submittxt|escape:'htmlall'}' class='LSform' /></dd>
      </dl>
      
    </div>
  {/foreach}  
  <!-- Tabs - End Content -->
{else}
  {if $LSformElement_image!=''}
    <div class='LSformElement_image{if $LSformElement_image_errors} LSformElement_image_errors{/if}'>
      {if $LSformElement_image_actions!='' && !$LSformElement_image_errors}
      <ul class='LSformElement_image_actions'>
          <li><img src='{img name='zoom'}' class='LSformElement_image_actions LSformElement_image_action_zoom' id='LSformElement_image_action_zoom_{$LSformElement_image.id|escape:'htmlall'}' /></li>
        {foreach from=$LSformElement_image_actions item=item}
          <li><img src='{img name=$item}' class='LSformElement_image_actions LSformElement_image_action_{$item|escape:'htmlall'}' id='LSformElement_image_action_{$item|escape:'htmlall'}_{$LSformElement_image.id|escape:'htmlall'}' /></li>
        {/foreach}
      </ul>
      {/if}
      <img src='{$LSformElement_image.img}' class='LSformElement_image LSsmoothbox' id='LSformElement_image_{$LSformElement_image.id|escape:'htmlall'}' />
    </div>
  {/if}
  
  <div class='LSform'>
    <dl class='LSform'>
      {foreach from=$LSform_fields item=field}
      <dt class='LSform{if $field.errors != ''} LSform-errors{/if}'>{$field.label}{if $field.required} *{/if}{if $field.help_info!=""} <img class='LStips' src="{img name='help'}" alt='?' title='{$field.help_info|escape:'htmlall'}'/>{/if}</dt>
      <dd class='LSform'>{$field.html}{if $field.add != ''} <span class='LSform-addfield'>+ Ajouter un champ</span>{/if}</dd>
      {if $field.errors != ''}
        {foreach from=$field.errors item=error}
        <dd class='LSform LSform-errors'>{$error|escape:'htmlall'}</dd>
        {/foreach}
      {/if}
      {/foreach}
      <dd class='LSform'><input type='submit' value='{$LSform_submittxt|escape:"htmlall"}' class='LSform' /></dd>
    </dl>
  </div>
  
{/if}
</form>
