{include file='ls:top.tpl'}
<div id='LSaccessRightsMatrixView'>
  <h1>{$pagetitle}</h1>
  <ul class="LSaccessRightsMatrixView_tabs">
  {foreach $LSobjects as $obj => $obj_conf}
    <li{if $LSobject==$obj} class="LSaccessRightsMatrixView_active_tab"{/if}><a href="addon/LSaccessRightsMatrixView/accessRightsMatrix&LSobject={$obj}">{$obj_conf.label}</a></li>
  {/foreach}
  </ul>

  <div class='LSaccessRightsMatrixView_tab_content'>
    <h2>{$LSobjects[$LSobject]['label']}</h2>

    <table class="table-header-rotated">
      <thead>
        <tr>
          <th>
            {tr msg="Attributes / Profiles"}
            <div id="LSaccessRightsMatrixView_legend">
              <label>{tr msg="Legend:"}</label>
              <span class='LSaccessRightsMatrixView_readable'>{tr msg="R"}</span> = {tr msg="Readable"}
              |
              <span class='LSaccessRightsMatrixView_writable'>{tr msg="R/W"}</span> = {tr msg="Readable / Writable"}
            </div>
          </th>
          {foreach $LSprofiles as $name => $label}
          <th class="rotate-45"><div><span>{if $name != $label}<img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/>{/if} {$label}</span></div></th>
          {/foreach}
        </tr>
      </thead>
      <tbody>
      {foreach $LSobjects[$LSobject]['attrs'] as $name => $conf}
        <tr>
          <th class="row-header">{$conf.label} <img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/></th>
          {foreach $LSprofiles as $profil => $profil_label}
          <td class='LStips' title="{if $profil != $profil_label}{$profil_label} ({$profil}){else}{$profil}{/if}">
          {if $conf.rights[$profil] == 'r'}
            <span class='LSaccessRightsMatrixView_readable'>{tr msg="R"}</span>
          {elseif $conf.rights[$profil] == 'w'}
            <span class='LSaccessRightsMatrixView_writable'>{tr msg="R/W"}</span>
          {/if}
          </td>
          {/foreach}
        </tr>
      {/foreach}
      </tbody>
    </table>

    <h3>{tr msg="Their relations with other objects"}</h3>
{if !empty($LSobjects[$LSobject]['relations'])}
    <table class="table-header-rotated">
      <thead>
        <th>
          {tr msg="Relations / Profiles"}
          <div id="LSaccessRightsMatrixView_legend">
            <label>{tr msg="Legend:"}</label>
            <span class='LSaccessRightsMatrixView_readable'>R</span> = {tr msg="Readable"}
            |
            <span class='LSaccessRightsMatrixView_writable'>R/W</span> = {tr msg="Readable / Writable"}
          </div>
        </th>
        {foreach $LSprofiles as $name => $label}
        <th class="rotate-45"><div><span>{if $name != $label}<img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/>{/if} {$label}</span></div></th>
        {/foreach}
      </thead>
      <tbody>
      {foreach $LSobjects[$LSobject]['relations'] as $name => $conf}
        <tr>
          <th class="row-header">{$conf.label} <img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/></th>
          {foreach $LSprofiles as $profil => $profil_label}
          <td class='LStips' title="{if $profil != $profil_label}{$profil_label} ({$profil}){else}{$profil}{/if}">
          {if $conf.rights[$profil] == 'r'}
            <span class='LSaccessRightsMatrixView_readable'>{tr msg="R"}</span>
          {elseif $conf.rights[$profil] == 'w'}
            <span class='LSaccessRightsMatrixView_writable'>{tr msg="R/W"}</span>
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
