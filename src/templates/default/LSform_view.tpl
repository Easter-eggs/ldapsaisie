<input type='hidden' name='LSform_objecttype' id='LSform_objecttype'  value='{$LSform_object.type|escape:"htmlall"}'/>
<input type='hidden' name='LSform_objectdn' id='LSform_objectdn'  value='{$LSform_object.dn|escape:"htmlall"}'/>
{if isset($LSform_layout) && $LSform_layout}
  <!-- Tabs - Start Title -->
  <ul class='LSform_layout'>
  {foreach from=$LSform_layout item=tab key=tab_key}
    <li class='LSform_layout' id='LSform_layout_btn_{$tab_key|escape:"htmlall"}'><a href='#{$tab_key|escape:"htmlall"}'>{tr msg=$tab.label}</a></li>
  {/foreach}
  </ul>
  <!-- Tabs - End Title -->

  <!-- Tabs - Start Content -->
  {foreach from=$LSform_layout item=tab key=tab_key}
    <a name='{$tab_key|escape:"htmlall"}'></a>
    <h2 class='LSform_layout'>{$tab.label|escape:"htmlall"}</h2>
    <div class='LSform LSform_layout' id='LSform_layout_div_{$tab_key|escape:"htmlall"}'>

      {if isset($LSformElement_image) && $LSformElement_image!='' && isset($tab['img']) && $tab.img==1}
        <div class='LSformElement_image'>
          <a href='{$LSformElement_image.img|escape:"htmlall"}' rel='rien ici' title='comment' class='mb'><img src='{$LSformElement_image.img|escape:"htmlall"}' class='LSformElement_image LSsmoothbox' id='LSformElement_image_{$LSformElement_image.id|escape:"htmlall"}' /></a>
        </div>
      {/if}

      <dl class='LSform'>
        {assign var='field' value='non'}
        {foreach from=$tab.args item=arg}
          {if isset($LSform_fields[$arg])}
            {assign var='field' value='oui'}
            <dt class='LSform'>{$LSform_fields[$arg].label|escape:"htmlall"}{if $LSform_fields[$arg].help_info_in_view && $LSform_fields[$arg].help_info!=""} <img class='LStips' src="{img name='help'}" alt='?' title='{$LSform_fields[$arg].help_info|escape:'htmlall'}'/>{/if}</dt>
            <dd class='LSform'>{$LSform_fields[$arg].html}</dd>
          {/if}
        {/foreach}
        {if $field=='non'}
          <dd class='LSform'>{$LSform_layout_nofield_label|escape:"htmlall"}</dd>
        {/if}
      </dl>

    </div>
  {/foreach}
  <!-- Tabs - End Content -->
{else}

  {if isset($LSformElement_image) && $LSformElement_image!=''}
  <div class='LSformElement_image'>
    <a href='{$LSformElement_image.img|escape:"htmlall"}' rel='rien ici' title='comment' class='mb'><img src='{$LSformElement_image.img|escape:"htmlall"}' class='LSformElement_image LSsmoothbox' id='LSformElement_image_{$LSformElement_image.id|escape:"htmlall"}' /></a>
  </div>
  {/if}

  <div class='LSform'>
    <dl class='LSform'>
      {foreach from=$LSform_fields item=field}
      <dt class='LSform'>{$field.label|escape:"htmlall"}</dt>
      <dd class='LSform'>{$field.html}</dd>
      {/foreach}
    </dl>
  </div>

{/if}
