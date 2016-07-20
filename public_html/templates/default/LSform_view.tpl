<form class='LSform form-horizontal'>
<input type='hidden' name='LSform_objecttype' id='LSform_objecttype'  value='{$LSform_object.type}'/>
<input type='hidden' name='LSform_objectdn' id='LSform_objectdn'  value='{$LSform_object.dn}'/>
{if $LSform_layout}
  <!-- Tabs - Start Title -->
  <ul class="nav nav-tabs">
  {foreach from=$LSform_layout item=tab key=tab_key}
    <li role="presentation" class='LSform_layout' id='LSform_layout_btn_{$tab_key}'><a href="#{$tab_key}">{tr msg=$tab.label}</a></li>
  {/foreach}
  </ul>
  <!-- Tabs - End Title -->
{/if}

<div class="row">
<div class="col-lg-8">

{if $LSform_layout}
  <!-- Tabs - Start Content -->
  {foreach from=$LSform_layout item=tab key=tab_key}
    <a name='{$tab_key}'></a>
    <h2 class='LSform_layout'>{$tab.label}</h2>
    <div class='LSform LSform_layout' id='LSform_layout_div_{$tab_key}'>
    
      <div class='LSform_container'>
        {assign var='field' value='non'}
        {foreach from=$tab.args item=arg}
          {if $LSform_fields[$arg]}
            {assign var='field' value='oui'}
            <div class="LSform_attribute form-group">
              <label class="col-md-4 control-label">{$LSform_fields[$arg].label}</label>
              <div class="col-md-8">{$LSform_fields[$arg].html}</div>
            </div>
          {/if}
        {/foreach}
        {if $field=='non'}
        <div class="form-group">
          <div class='col-md-offset-4 col-md-8'>
            {$LSform_layout_nofield_label}
          </div>
        </div>
        {/if}
      </div>

    </div>
  {/foreach}  
  <!-- Tabs - End Content -->
{else}

  <div class='LSform'>
    <div class='LSform_container'>
      {foreach from=$LSform_fields item=field}
      <div class="LSform_attribute form-group">
        <label class="col-md-4 control-label">{$field.label}</label>
        <div class="col-md-8">{$field.html}</div>
      </div>
      {/foreach}
    </div>
  </div>
   
{/if}
</div>
<div class="col-lg-4">
   {if $LSformElement_image!=''}
     <div class='LSformElement_image'>
       <a href='{$LSformElement_image.img}' rel='rien ici' title='comment' class='mb'><img src='{$LSformElement_image.img}' class='LSformElement_image LSsmoothbox' id='LSformElement_image_{$LSformElement_image.id}' /></a>
     </div>
   {/if}
</div>
</div>

</form>
