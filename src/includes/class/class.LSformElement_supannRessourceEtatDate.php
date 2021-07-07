<?php
/*******************************************************************************
 * Copyright (C) 2021 Easter-eggs
 * https://ldapsaisie.org
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

LSsession :: loadLSclass('LSformElement_supannCompositeAttribute');
LSsession :: loadLSaddon('supann');

/**
 * Element supannRessourceEtatDate d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments supannRessourceEtatDate des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannRessourceEtatDate extends LSformElement_supannCompositeAttribute {

  public function __construct(&$form, $name, $label, $params, &$attr_html){
    $this -> components = array (
      'ressource' => array (
        'label' => _('Resource'),
        'type' => 'select',
        'possible_values' => array('' => '-'),
        'get_possible_values' => 'supannGetRessourcePossibleValues',
        'required' => true,
      ),
      'etat' => array (
        'label' => _('State'),
        'type' => 'select',
        'possible_values' => array('' => '-'),
        'get_possible_values' => 'supannGetRessourceEtatPossibleValues',
        'required' => true,
      ),
      'sous_etat' => array (
        'label' => _('Sub-state'),
        'type' => 'select',
        'possible_values' => array('' => '-'),
        'get_possible_values' => 'supannGetRessourceSousEtatPossibleValues',
        'required' => false,
      ),
      'date_debut' => array (
        'label' => _('Start date'),
        'type' => 'date',
        'format' => 'Ymd',
        'required' => false,
      ),
      'date_fin' => array (
        'label' => _('End date'),
        'type' => 'date',
        'format' => 'Ymd',
        'required' => false,
      ),
    );
    return parent :: __construct($form, $name, $label, $params, $attr_html);
  }

  /**
   * Parse une valeur composite gérer par ce type d'attribut
   *
   * @param  $value string La valeur à parser
   * @return array|null La valeur parsée, ou NULL en cas de problème
   */
  public function parseCompositeValue($value) {
    if (preg_match('/\{(?<ressource>[^\}]+)\}(?<etat>[^:]+):(?<sous_etat>[^\:]*):(?<date_debut>[0-9]{8})?:(?<date_fin>[0-9]{8})?/', $value, $matches)) {
      $parseValue = array(
        'ressource' => $matches['ressource'],
        'etat' => $matches['etat'],
      );
      foreach(array('sous_etat', 'date_debut', 'date_fin') as $c)
        if (isset($matches[$c]) && !is_empty($matches[$c]))
          $parseValue[$c] = $matches[$c];
      return $parseValue;
    }
    return;
  }

  /**
   * Format une valeur composite gérer par ce type d'attribut
   *
   * @param  $value string La valeur à parser
   * @return array|null|false La valeur formatée, NULL en cas de valeur vide, ou False en cas de problème
   */
  public function formatCompositeValue($value) {
    if (is_array($value)) {
      if (!$value['ressource'] || !$value['etat'])
        return null;
      $ret = "{".$value['ressource']."}".$value['etat'];
      foreach(array('sous_etat', 'date_debut', 'date_fin') as $c) {
        $ret .= ":";
        if (isset($value[$c]) && !is_empty($value[$c]))
          $ret .= $value[$c];
      }
      return $ret;
    }
    return False;
  }

}
