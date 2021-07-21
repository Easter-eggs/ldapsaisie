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

/*
 ***********************************************
 * Dynamic group configuration
 ***********************************************
 */

// Dynamic group object type
define('DYNGROUP_OBJECT_TYPE', 'LSdyngroup');

/*
 * Members DN attributes
 */

// Members DN URI attribute
define('DYNGROUP_MEMBER_DN_URI_ATTRIBUTE', 'lsDynGroupMemberDnURI');

// Members DN attribute
define('DYNGROUP_MEMBER_DN_ATTRIBUTE', 'lsDynGroupMemberDn');

// Members DN static attribute
define('DYNGROUP_MEMBER_DN_STATIC_ATTRIBUTE', 'uniqueMember');

/*
 * Members UID attributes
 */

// Members UID URI attribute
define('DYNGROUP_MEMBER_UID_URI_ATTRIBUTE', 'lsDynGroupMemberUidURI');

// Members UID attribute
define('DYNGROUP_MEMBER_UID_ATTRIBUTE', 'lsDynGroupMemberUid');

// Members UID static attribute
define('DYNGROUP_MEMBER_UID_STATIC_ATTRIBUTE', 'memberUid');
