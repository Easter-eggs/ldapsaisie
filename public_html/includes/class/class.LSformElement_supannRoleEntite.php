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

LSsession :: loadLSclass('LSformElement_supannCompositeAttribute');
LSsession :: loadLSaddon('supann');

/**
 * Element supannRoleEntite d'un formulaire pour LdapSaisie
 *
 * Cette classe définis les éléments supannRoleEntite des formulaires.
 * Elle étant la classe basic LSformElement.
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */

class LSformElement_supannRoleEntite extends LSformElement_supannCompositeAttribute {

  function LSformElement_supannRoleEntite (&$form, $name, $label, $params,&$attr_html){
	  $this -> components = array (
		  'role' => array (
			'label' => _('Role'),
			'type' => 'table',
			'table' => 'roleGenerique',
			'required' => true,
		  ),
		  'type' => array (
			'label' => _('Entity type'),
			'type' => 'table',
			'table' => 'typeEntite',
			'required' => true,
		  ),
		  'code' => array (
			'label' => _('Entity'),
			'type' => 'codeEntite',
			'required' => false
		  )
	  );
	  return parent::LSformElement ($form, $name, $label, $params,$attr_html);
  }

}
