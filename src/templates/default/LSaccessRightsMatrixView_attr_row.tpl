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
