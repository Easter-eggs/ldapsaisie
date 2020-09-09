<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
 * http://ldapsaisie.labs.libre-entreprise.org
 *
 * Author: See AUTHORS file in top-level directory.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

******************************************************************************/

LSsession :: loadLSclass('LSattr_html_select_box');

/**
 * HTML attribute type for sambaAcctFlags
 */
class LSattr_html_sambaAcctFlags extends LSattr_html_select_box {

  var $LSformElement_type = 'sambaAcctFlags';

  /**
   * Retourne un tableau des valeurs possibles de la liste
   *
   * @param[in] $options Attribute options (optional)
   * @param[in] $name Attribute name (optional)
   * @param[in] &$ldapObject Related LSldapObject (optional)
   *
   * @author Benjamin Renard <brenard@easter-eggs.com>
   *
   * @retval array Tableau associatif des valeurs possible de la liste avec en clé
   *               la valeur des balises option et en valeur ce qui sera affiché.
   */
  public static function _getPossibleValues($options=false, $name=false, &$ldapObject=false) {
    $retInfos = array();
    if (!LSsession :: loadLSclass('LSattr_ldap_sambaAcctFlags', null, true))
      return $retInfos;
    foreach(LSattr_ldap_sambaAcctFlags :: get_available_flags() as $group_label => $flags) {
      $retInfos[] = array(
        'label' => $group_label,
        'possible_values' => $flags,
      );
    }
    return $retInfos;
  }

}
