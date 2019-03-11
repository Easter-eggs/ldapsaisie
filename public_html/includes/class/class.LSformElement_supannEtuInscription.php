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

LSsession :: loadLSclass('LSformElement');
LSsession :: loadLSclass('LSformElement_supannCompositeAttribute');
LSsession :: loadLSaddon('supann');

/**
 * Element supannEtuInscription d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments supannEtuInscription des formulaires.
 * Elle étant la classe LSformElement_supannCompositeAttribute.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannEtuInscription extends LSformElement_supannCompositeAttribute {

	function LSformElement_supannEtuInscription (&$form, $name, $label, $params,&$attr_html){
		$this -> components = array (
			'etab' => array (
				'label' => _('Organism'),
				'type' => 'table',
				'table' => 'codeEtablissement',
				'required' => true,
			),
			'anneeinsc' => array (
				'label' => _('Registration year'),
				'type' => 'text',
				'required' => true,
				'check_data' => array (
					'integer' => array (
						'msg' => _('Registration year must be an integer'),
						'params' => array (
							'positive' => true,
							'min' => 1970
						)
					),
				)
			),
			'regimeinsc' => array (
				'label' => _('Registration regime'),
				'type' => 'table',
				'table' => 'etuRegimeInscription',
				'required' => true
			),
			'sectdisc' => array (
				'label' => _('Discipline sector'),
				'type' => 'table',
				'table' => 'etuSecteurDisciplinaire',
				'required' => true
			),
			'typedip' => array (
				'label' => _('Diploma type'),
				'type' => 'table',
				'table' => 'etuTypeDiplome',
				'required' => true
			),
			'cursusann' => array (
				'label' => _('Cursus & Year'),
				'type' => 'text',
				'check_data' => array (
					'regex' => array (
						'params' => array (
							'regex' => '/^[LMDXB][0-9]?$/'
						)
					),
				),
				'required' => true
			),
			'affect' => array (
				'label' => _('Entity'),
				'type' => 'codeEntite',
				'required' => false
			),
			'diplome' => array (
				'label' => _('Diploma'),
				'type' => 'table',
				'table' => 'etuDiplome',
				'required' => false
			),
			'etape' => array (
				'label' => _('Step'),
				'type' => 'table',
				'table' => 'etuEtape',
				'required' => false
			),
			'eltpedago' => array (
				'label' => _('Pedagogical element'),
				'type' => 'table',
				'table' => 'etuElementPedagogique',
				'required' => false
			)
		);
		return parent::LSformElement ($form, $name, $label, $params,$attr_html);
	}

}

