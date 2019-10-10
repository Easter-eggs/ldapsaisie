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
        {foreach $LSprofiles as $name => $conf}
        <th>{$name}</th>
        {/foreach}
      </thead>
      <tbody>
      {foreach $LSobjects[$LSobject]['attrs'] as $name => $conf}
        <tr>
          <th>{$conf.label}</th>
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
  </div>
</div>
{include file='ls:bottom.tpl'}
