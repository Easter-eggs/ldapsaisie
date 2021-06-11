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
 * Element supannEmpProfil d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments supannEmpProfil des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannEmpProfil extends LSformElement_supannCompositeAttribute {

  public function __construct(&$form, $name, $label, $params, &$attr_html){
    $this -> components = array (
      'etab' => array (
        'label' => _('Establishment'),
        'type' => 'table',
        'table' => 'codeEtablissement',
        'required' => true,
      ),
      'affil' => array (
        'label' => _('EduPerson profil'),
        'type' => 'select',
        'possible_values' => array('' => '-'),
        'get_possible_values' => 'supannGetAffiliationPossibleValues',
        'required' => false,
      ),
      'corps' => array (
        'label' => _('Body of membership'),
        'type' => 'table',
        'table' => 'empCorps',
        'required' => false,
      ),
      'typeaffect' => array (
        'label' => _('Entity type'),
        'type' => 'table',
        'table' => 'typeEntite',
        'required' => false,
      ),
      'affect' => array (
        'label' => _('Assignment entity'),
        'type' => 'codeEntite',
        'required' => false,
      ),
      'activite' => array (
        'label' => _('Activity'),
        'type' => 'table',
        'table' => 'supannActivite',
        'required' => false,
      ),
      'population' => array (
        'label' => _('Population'),
        'type' => 'table',
        'table' => 'codePopulation',
        'required' => false,
      ),
      'datefin' => array (
        'label' => _('End date'),
        'type' => 'date',
        'required' => false,
      ),
    );
    return parent :: __construct($form, $name, $label, $params, $attr_html);
  }

}
