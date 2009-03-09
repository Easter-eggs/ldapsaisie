<input type='hidden' name='LSform_objecttype' id='LSform_objecttype'  value='{$LSform_object.type}'/>
<input type='hidden' name='LSform_objectdn' id='LSform_objectdn'  value='{$LSform_object.dn}'/>
{if $LSform_layout}
  <!-- Tabs - Start Title -->
  <ul class='LSform_layout'>
  {foreach from=$LSform_layout item=tab key=tab_key}
    <li class='LSform_layout' id='LSform_layout_btn_{$tab_key}'><a href="#{$tab_key}">{$tab.label}</a></li>
  {/foreach}
  </ul>
  <!-- Tabs - End Title -->

  <!-- Tabs - Start Content -->
  {foreach from=$LSform_layout item=tab key=tab_key}
    <a name='{$tab_key}'></a>
    <h2 class='LSform_layout'>{$tab.label}</h2>
    <div class='LSform LSform_layout' id='LSform_layout_div_{$tab_key}'>
    
      {if $LSformElement_image!='' && $tab.img==1}
        <div class='LSformElement_image'>
          <a href='{$LSformElement_image.img}' rel='rien ici' title='comment' class='mb'><img src='{$LSformElement_image.img}' class='LSformElement_image LSsmoothbox' id='LSformElement_image_{$LSformElement_image.id}' /></a>
        </div>
      {/if}
      
      <dl class='LSform'>
        {assign var='field' value='non'}
        {foreach from=$tab.args item=arg}
          {if $LSform_fields[$arg]}
            {assign var='field' value='oui'}
            <dt class='LSform'>{$LSform_fields[$arg].label}</dt>
            <dd class='LSform'>{$LSform_fields[$arg].html}</dd>
          {/if}
        {/foreach}
        {if $field=='non'}
          <dd class='LSform'>{$LSform_layout_nofield_label}</dd>
        {/if}
      </dl>
      
    </div>
  {/foreach}  
  <!-- Tabs - End Content -->
{else}

  {if $LSformElement_image!=''}
  <div class='LSformElement_image'>
    <a href='{$LSformElement_image.img}' rel='rien ici' title='comment' class='mb'><img src='{$LSformElement_image.img}' class='LSformElement_image LSsmoothbox' id='LSformElement_image_{$LSformElement_image.id}' /></a>
  </div>
  {/if}

  <div class='LSform'>
    <dl class='LSform'>
      {foreach from=$LSform_fields item=field}
      <dt class='LSform'>{$field.label}</dt>
      <dd class='LSform'>{$field.html}</dd>
      {/foreach}
    </dl>
  </div>
   
{/if}
