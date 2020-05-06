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
 ***********************************************
 * Configuration du support de l'envoi de mail *
 ***********************************************
 */

// Pear :: Mail
define('PEAR_MAIL','/usr/share/php/Mail.php');

// Pear :: Mail_mime
define('PEAR_MAIL_MIME','/usr/share/php/Mail/mime.php');

/*
 * Méthode d'envoie :
 *  - mail : envoie avec la méthode PHP mail()
 *  - sendmail : envoie la commande sendmail du système
 *  - smtp : envoie en utilisant un serveur SMTP
 */
define('MAIL_SEND_METHOD','smtp');

/*
 * Paramètres d'envoie :
 *   Ces paramètres dépende de la méthode utilisé. Repporté vous à la documentation
 * de PEAR :: Mail pour plus d'information.
 * Lien : http://pear.php.net/manual/en/package.mail.mail.factory.php
 * Infos :
 *  List of parameter for the backends
 *  mail
 *    o If safe mode is disabled, $params will be passed as the fifth
 *      argument to the PHP mail() function. If $params is an array,
 *      its elements will be joined as a space-delimited string.
 *  sendmail
 *    o $params["sendmail_path"] - The location of the sendmail program
 *      on the filesystem. Default is /usr/bin/sendmail.
 *    o $params["sendmail_args"] - Additional parameters to pass to the
 *      sendmail. Default is -i.
 *  smtp
 *    o $params["host"] - The server to connect. Default is localhost.
 *    o $params["port"] - The port to connect. Default is 25.
 *    o $params["auth"] - Whether or not to use SMTP authentication.
 *      Default is FALSE.
 *    o $params["username"] - The username to use for SMTP authentication.
 *    o $params["password"] - The password to use for SMTP authentication.
 *    o $params["localhost"] - The value to give when sending EHLO or HELO.
 *      Default is localhost
 *    o $params["timeout"] - The SMTP connection timeout.
 *      Default is NULL (no timeout).
 *    o $params["verp"] - Whether to use VERP or not. Default is FALSE.
 *    o $params["debug"] - Whether to enable SMTP debug mode or not.
 *      Default is FALSE.
 *    o $params["persist"] - Indicates whether or not the SMTP connection
 *      should persist over multiple calls to the send() method.
 */
$MAIL_SEND_PARAMS = NULL;

/*
 * Headers :
 */
$MAIL_HEARDERS = array(
);
