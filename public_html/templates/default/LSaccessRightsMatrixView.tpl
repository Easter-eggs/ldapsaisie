{include file='ls:top.tpl'}
<div id='LSaccessRightsMatrixView'>
  <h1>{$pagetitle}</h1>
  <ul class="LSaccessRightsMatrixView_tabs">
  {foreach $LSobjects as $obj => $obj_conf}
    <li{if $LSobject==$obj} class="LSaccessRightsMatrixView_active_tab"{/if}><a href="addon_view.php?LSaddon=LSaccessRightsMatrixView&view=accessRightsMatrix&LSobject={$obj}">{$obj_conf.label}</a></li>
  {/foreach}
  </ul>

  <div class='LSaccessRightsMatrixView_tab_content'>
    <h2>{$LSobjects[$LSobject]['label']}</h2>
   
    <table>
      <thead>
        <th>{tr msg="Attributes / Profiles"}</th>
        {foreach $LSprofiles as $name => $label}
        <th>{$label} {if $name != $label}<img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/>{/if}</th>
        {/foreach}
      </thead>
      <tbody>
      {foreach $LSobjects[$LSobject]['attrs'] as $name => $conf}
        <tr>
          <th>{$conf.label} <img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/></th>
          {foreach $LSprofiles as $profil => $profil_conf}
          <td>
          {if $conf.rights[$profil] == 'r'}
            <span class='LSaccessRightsMatrixView_readable'>{tr msg="Readable"}</span>
          {elseif $conf.rights[$profil] == 'w'}
            <span class='LSaccessRightsMatrixView_writable'>{tr msg="Readable / Writable"}</span>
          {/if}
          </td>
          {/foreach}
        </tr>
      {/foreach}
      </tbody>
    </table>

    <h3>{tr msg="Their relations with other objects"}</h3>
{if !empty($LSobjects[$LSobject]['relations'])}
    <table>
      <thead>
        <th>{tr msg="Relations / Profiles"}</th>
        {foreach $LSprofiles as $name => $label}
        <th>{$label} {if $name != $label}<img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/>{/if}</th>
        {/foreach}
      </thead>
      <tbody>
      {foreach $LSobjects[$LSobject]['relations'] as $name => $conf}
        <tr>
          <th>{$conf.label} <img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/></th>
          {foreach $LSprofiles as $profil => $profil_conf}
          <td>
          {if $conf.rights[$profil] == 'r'}
            <span class='LSaccessRightsMatrixView_readable'>{tr msg="Readable"}</span>
          {elseif $conf.rights[$profil] == 'w'}
            <span class='LSaccessRightsMatrixView_writable'>{tr msg="Readable / Writable"}</span>
          {/if}
          </td>
          {/foreach}
        </tr>
      {/foreach}
      </tbody>
    </table>
{else}
    <p>{tr msg="This object type has no configured relation."}
{/if}
  </div>
</div>
{include file='ls:bottom.tpl'}
