<form action='{$LSform_action}' method='post' enctype="multipart/form-data" class='LSform form-horizontal'>
{$LSform_header}
{if $LSform_layout}
  <!-- Tabs - Start Title -->
  <ul class="nav nav-tabs">
  {foreach from=$LSform_layout item=tab key=tab_key}
    <li class='LSform_layout' id='LSform_layout_btn_{$tab_key}'><a href="#{$tab_key}">{tr msg=$tab.label}</a></li>
  {/foreach}
  </ul>
  <!-- Tabs - End Title -->

<div class="row">
<div class="col-lg-8">

  <!-- Tabs - Start Content -->
  {foreach from=$LSform_layout item=tab key=tab_key}
    <a name='{$tab_key}'></a>
    <h2 class='LSform_layout'>{tr msg=$tab.label}</h2>
    <div class='LSform LSform_layout' id='LSform_layout_div_{$tab_key}'>
      
      <div class='LSform_container'>
        {foreach from=$tab.args item=arg}
          {if $LSform_fields[$arg]}
            <div class="LSform_attribute form-group{if $LSform_fields[$arg].errors != ''} has-error{/if}">
              <label class="col-md-4 control-label">{$LSform_fields[$arg].label}{if $LSform_fields[$arg].required} *{/if}{if $LSform_fields[$arg].help_info!=""} <img class='LStips' src="{img name='help'}" alt='?' title="{$LSform_fields[$arg].help_info}"/>{/if}</label>
              <div class="col-md-8">{$LSform_fields[$arg].html}{if $LSform_fields[$arg].add != ''} <span class='LSform-addfield'>+ Ajouter un champ</span>{/if}</div>
            </div>
            {if $LSform_fields[$arg].errors != ''}
              {foreach from=$LSform_fields[$arg].errors item=error}
            <div class="form-group">
              <div class='col-md-offset-4 col-md-8 has-error LSform-errors'>{$error}</div>
            </div>
              {/foreach}
            {/if}
          {/if}
        {/foreach}
        <div class="form-group">
          <div class='col-md-offset-4 col-md-8'>
            <button type="submit" class="btn btn-default">{$LSform_submittxt}</button>
          </div>
        </div>
      </div>
      
    </div>
  {/foreach}  
  <!-- Tabs - End Content -->
</div>
<div class="col-lg-4">
  {if $LSformElement_image!=''}
  <div class='LSformElement_image{if $LSformElement_image_errors} LSformElement_image_errors{/if}'>
    {if $LSformElement_image_actions!='' && !$LSformElement_image_errors}
    <ul class='LSformElement_image_actions'>
        <li><img src='{img name="zoom"}' class='LSformElement_image_actions LSformElement_image_action_zoom' id='LSformElement_image_action_zoom_{$LSformElement_image.id}' /></li>
      {foreach from=$LSformElement_image_actions item=item}
        <li><img src='{img name=$item}' class='LSformElement_image_actions LSformElement_image_action_{$item}' id='LSformElement_image_action_{$item}_{$LSformElement_image.id}' /></li>
      {/foreach}
    </ul>
    {/if}
    <img src='{$LSformElement_image.img}' class='LSformElement_image LSsmoothbox' id='LSformElement_image_{$LSformElement_image.id}' />
  </div>
  {/if}
</div>
</div>

{else}
  {if $LSformElement_image!=''}
    <div class='LSformElement_image{if $LSformElement_image_errors} LSformElement_image_errors{/if}'>
      {if $LSformElement_image_actions!='' && !$LSformElement_image_errors}
      <ul class='LSformElement_image_actions'>
          <li><img src='{img name='zoom'}' class='LSformElement_image_actions LSformElement_image_action_zoom' id='LSformElement_image_action_zoom_{$LSformElement_image.id}' /></li>
        {foreach from=$LSformElement_image_actions item=item}
          <li><img src='{img name=$item}' class='LSformElement_image_actions LSformElement_image_action_{$item}' id='LSformElement_image_action_{$item}_{$LSformElement_image.id}' /></li>
        {/foreach}
      </ul>
      {/if}
      <img src='{$LSformElement_image.img}' class='LSformElement_image LSsmoothbox' id='LSformElement_image_{$LSformElement_image.id}' />
    </div>
  {/if}
  
  <div class='LSform_container'>
    {foreach from=$LSform_fields item=field}
    <div class="form-group{if $field.errors != ''} has-error{/if}">
      <label class="col-md-4 control-label">{$field.label}{if $field.required} *{/if}{if $field.help_info!=""} <img class='LStips' src="{img name='help'}" alt='?' title="{$field.help_info}"/>{/if}</label>
      <div class="col-md-8">{$field.html}{if $field.add != ''} <span class='LSform-addfield'>+ Ajouter un champ</span>{/if}</div>
    </div>
      {if $field.errors != ''}
        {foreach from=$field.errors item=error}
        <div class="form-group">
          <div class='col-md-offset-4 col-md-8 has-error LSform-errors'>{$error}</div>
        </div>
        {/foreach}
      {/if}
    {/foreach}
    <div class="form-group">
      <div class='col-md-offset-4 col-md-8'>
        <button type="submit" class="btn btn-default">{$LSform_submittxt}</button>
      </div>
    </div>
  </div>
  
{/if}
</form>
