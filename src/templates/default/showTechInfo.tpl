{extends file='ls:base_connected.tpl'}
{block name="content"}
<div id='showTechInfo'>
  <h1>{$pagetitle}</h1>
  {include file='ls:LSview_actions.tpl'}
  <dl>
    <dt>DN</dt>
    <dd><span class="copyable">{$object->getDn()|escape:"htmlall"}</span></dd>

    {if $object_classes}
    <dt>{tr msg='Object classes'}</dt>
    <dd>
      <ul>
      {foreach $object_classes as $class}
        {if $structural_object_class == $class}
        <li><strong class="copyable">{$class|escape:"htmlall"}</strong> <img class='LStips' src="{img name='help'}" alt='?' title="{tr msg="Structural object class"|escape:"htmlall"}"/></li>
        {else}
        <li><span class="copyable">{$class|escape:"htmlall"}</span></li>
        {/if}
      {/foreach}
      </ul>
    </dd>
    {/if}

    {foreach $special_internal_attributes as $attr => $info}
      <dt>{$info.label} <img class='LStips' src="{img name='help'}" alt='?' title="{$attr|escape:'htmlall'}"/></dt>
      <dd>
        {if is_array($info.values)}
        <ul>
          {foreach $info.values as $value}
          <li><span class="copyable">{$value|escape:"htmlall"}</span></li>
          {/foreach}
        </ul>
        {else}
          <span class="copyable">{$info.values|escape:"htmlall"}</span>
        {/if}
      </dd>
    {/foreach}

    {if $other_internal_attrs}
    <dt>{tr msg="Other internal attributes"}</dt>
    <dd>
      <dl>
      {foreach $other_internal_attrs as $attr => $values}
        {if $attr == 'objectClass'}{continue}{/if}
        <dt><span class="copyable">{$attr|escape:"htmlall"}</span></dt>
        <dd>
          {if is_array($values)}
          <ul>
            {foreach $values as $value}
            <li><span class="copyable">{$value|escape:"htmlall"}</span></li>
            {/foreach}
          </ul>
          {else}
            <span class="copyable">{$values|escape:"htmlall"}</span>
          {/if}
        </dd>
      {/foreach}
      </dl>
    </dd>
    {/if}
  </dl>
</div>
{/block}
