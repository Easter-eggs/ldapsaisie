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

/*
 ***********************************************************
 * Configuration of the HTTP authentification support *
 ***********************************************************
 */

// Don't check HTTP server's login/password by LDAP authentication challenge
//define('LSAUTHMETHOD_HTTP_TRUST_WITHOUT_PASSWORD_CHALLENGE',true);

/*
 * Set the HTTP server's method to pass authentifcated user/password informations
 * to PHP :
 *  - PHP_PASS : server define the PHP_AUTH_USER and PHP_AUTH_PW environnement
 *               variables. This is the default way using mod_php.
 *  - REMOTE_USER : server define the REMOTE_USER environnement variable. By using
 *               this method, only the user is pass by HTTP server to PHP and it
 *               could be only used if you enable the "don't check HTTP server's
 *               login/password by LDAP authentication challenge" option.
 *  - AUTHORIZATION : server pass HTTP Authorization header value to PHP by setting
 *               the HTTP_AUTHORIZATION environnement variable. This way could
 *               be use when using PHP in CGI-mode or with PHP-FPM. When using
 *               Apache, you could pass this information by using the rewrite module
 *               and setting the following rewrite rule :
 *               RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
 */
//define('LSAUTHMETHOD_HTTP_METHOD', 'PHP_PASS');

// Remote logout URL (in SSO context for instance)
//define('LSAUTHMETHOD_HTTP_LOGOUT_REMOTE_URL', 'https://idp.domain.tld/logout');
