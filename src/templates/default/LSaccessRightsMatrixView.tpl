{extends file='ls:base_connected.tpl'}
{block name="content"}
<div id='LSaccessRightsMatrixView'>
  <h1>{$pagetitle}</h1>
  <ul class="LSaccessRightsMatrixView_tabs">
  {foreach $LSobjects as $obj => $obj_conf}
    <li{if $LSobject==$obj} class="LSaccessRightsMatrixView_active_tab"{/if}><a href="addon/LSaccessRightsMatrixView/accessRightsMatrix?LSobject={$obj}">{$obj_conf.label}</a></li>
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
              <p><label>{tr msg="Legend:"}</label></p>
              <p>
                <span class='LSaccessRightsMatrixView_readable'>{tr msg="R"}</span> = {tr msg="Readable"}
                |
                <span class='LSaccessRightsMatrixView_writable'>{tr msg="R/W"}</span> = {tr msg="Readable / Writable"}
              </p>
              <p>
                <span class='LSaccessRightsMatrixView_readable LSaccessRightsMatrixView_inherit'>{tr msg="R"}</span> /
                <span class='LSaccessRightsMatrixView_writable LSaccessRightsMatrixView_inherit'>{tr msg="R/W"}</span>
                = {tr msg="Right inherited from all connected users profile"}
              </p>
            </div>
          </th>
          {foreach $LSprofiles as $name => $label}
          <th class="rotate-45"><div><span>{if $name != $label}<img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/>{/if} {$label}</span></div></th>
          {/foreach}
        </tr>
      </thead>
      <tbody>
      {if $LSobjects[$LSobject]['layout']}
        {foreach $LSobjects[$LSobject]['layout'] as $tab_name => $tab}
          <tr>
            <th colspan="{count($LSprofiles)+1}" class="LSaccessRightsMatrixView_layout_label">{$tab.label}</th>
          </tr>
          {foreach $tab.attrs as $name}
          {if !isset($LSobjects[$LSobject]['attrs'][$name])}{continue}{/if}
          {assign var=conf value=$LSobjects[$LSobject]['attrs'][$name]}
          {include file='ls:LSaccessRightsMatrixView_attr_row.tpl'}
          {/foreach}
        {/foreach}
      {else}
        {foreach $LSobjects[$LSobject]['attrs'] as $name => $conf}
        {include file='ls:LSaccessRightsMatrixView_attr_row.tpl'}
        {/foreach}
      {/if}
      </tbody>
    </table>

    <h3>{tr msg="Their relations with other objects"}</h3>
{if !empty($LSobjects[$LSobject]['relations'])}
    <table class="table-header-rotated">
      <thead>
        <th>
          {tr msg="Relations / Profiles"}
          <div id="LSaccessRightsMatrixView_legend">
            <p><label>{tr msg="Legend:"}</label></p>
            <p>
              <span class='LSaccessRightsMatrixView_readable'>{tr msg="R"}</span> = {tr msg="Readable"}
              |
              <span class='LSaccessRightsMatrixView_writable'>{tr msg="R/W"}</span> = {tr msg="Readable / Writable"}
            </p>
            <p>
              <span class='LSaccessRightsMatrixView_readable LSaccessRightsMatrixView_inherit'>{tr msg="R"}</span> /
              <span class='LSaccessRightsMatrixView_writable LSaccessRightsMatrixView_inherit'>{tr msg="R/W"}</span>
              = {tr msg="Right inherited from all connected users profile"}
            </p>
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
          {if $conf.rights[$profil] == 'w'}
            <span class='LSaccessRightsMatrixView_writable'>{tr msg="R/W"}</span>
          {elseif $profil != 'user' && $conf.rights['user'] == 'w'}
            <span class='LSaccessRightsMatrixView_writable LSaccessRightsMatrixView_inherit'>{tr msg="R/W"}</span>
          {elseif $conf.rights[$profil] == 'r'}
            <span class='LSaccessRightsMatrixView_readable'>{tr msg="R"}</span>
          {elseif $profil != 'user' && $conf.rights['user'] == 'r'}
            <span class='LSaccessRightsMatrixView_readable LSaccessRightsMatrixView_inherit'>{tr msg="R"}</span>
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

    <h3>{tr msg="Custom actions"}</h3>
{if !empty($LSobjects[$LSobject]['customActions'])}
    <table class="table-header-rotated">
      <thead>
        <th>
          {tr msg="Custom actions / Profiles"}
          <div id="LSaccessRightsMatrixView_legend">
            <p><label>{tr msg="Legend:"}</label></p>
            <p><span class='LSaccessRightsMatrixView_allowed'>X</span> = {tr msg="Allowed"}</p>
            <p>
              <span class='LSaccessRightsMatrixView_allowed LSaccessRightsMatrixView_inherit'>X</span>
              = {tr msg="Right inherited from all connected users profile"}
            </p>
          </div>
        </th>
        {foreach $LSprofiles as $name => $label}
        <th class="rotate-45"><div><span>{if $name != $label}<img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/>{/if} {$label}</span></div></th>
        {/foreach}
      </thead>
      <tbody>
      {foreach $LSobjects[$LSobject]['customActions'] as $name => $conf}
        <tr>
          <th class="row-header">{$conf.label} <img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/></th>
          {foreach $LSprofiles as $profil => $profil_label}
          <td class='LStips' title="{if $profil != $profil_label}{$profil_label} ({$profil}){else}{$profil}{/if}">
          {if $conf.rights[$profil]}
            <span class='LSaccessRightsMatrixView_allowed'>X</span>
          {elseif $profil != 'user' && $conf.rights['user']}
            <span class='LSaccessRightsMatrixView_allowed LSaccessRightsMatrixView_inherit'>X</span>
          {/if}
          </td>
          {/foreach}
        </tr>
      {/foreach}
      </tbody>
    </table>
{else}
    <p>{tr msg="This object type has no configured custom action."}
{/if}

    <h3>{tr msg="Custom search actions"}</h3>
{if !empty($LSobjects[$LSobject]['customSearchActions'])}
    <table class="table-header-rotated">
      <thead>
        <th>
          {tr msg="Custom actions / Profiles"}
          <div id="LSaccessRightsMatrixView_legend">
            <p><label>{tr msg="Legend:"}</label></p>
            <p><span class='LSaccessRightsMatrixView_allowed'>X</span> = {tr msg="Allowed"}</p>
            <p>
              <span class='LSaccessRightsMatrixView_allowed LSaccessRightsMatrixView_inherit'>X</span>
              = {tr msg="Right inherited from all connected users profile"}
            </p>
          </div>
        </th>
        {foreach $LSprofiles as $name => $label}
        <th class="rotate-45"><div><span>{if $name != $label}<img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/>{/if} {$label}</span></div></th>
        {/foreach}
      </thead>
      <tbody>
      {foreach $LSobjects[$LSobject]['customSearchActions'] as $name => $conf}
        <tr>
          <th class="row-header">{$conf.label} <img class='LStips' src="{img name='help'}" alt='?' title='{$name|escape:'htmlall'}'/></th>
          {foreach $LSprofiles as $profil => $profil_label}
          <td class='LStips' title="{if $profil != $profil_label}{$profil_label} ({$profil}){else}{$profil}{/if}">
          {if $conf.rights[$profil]}
            <span class='LSaccessRightsMatrixView_allowed'>X</span>
          {elseif $profil != 'user' && $conf.rights['user']}
            <span class='LSaccessRightsMatrixView_allowed LSaccessRightsMatrixView_inherit'>X</span>
          {/if}
          </td>
          {/foreach}
        </tr>
      {/foreach}
      </tbody>
    </table>
{else}
    <p>{tr msg="This object type has no configured custom search action."}
{/if}
  </div>
</div>
{/block}
