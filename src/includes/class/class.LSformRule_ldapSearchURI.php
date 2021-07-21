<?php
/*******************************************************************************
 * Copyright (C) 2007 Easter-eggs
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

/**
 * LSform rule to check a LDAP search URI
 *
 * @author Benjamin Renard <brenard@easter-eggs.com>
 */
class LSformRule_ldapSearchURI extends LSformRule {

  // CLI parameters autocompleters
  protected static $cli_params_autocompleters = array(
    'check_resolving_ldap_host' => array('LScli', 'autocomplete_bool'),
    'host_required' => array('LScli', 'autocomplete_bool'),
    'scope_required' => array('LScli', 'autocomplete_bool'),
    'attr_required' => array('LScli', 'autocomplete_bool'),
    'max_attrs_count' => array('LScli', 'autocomplete_int'),
    'filter_required' => array('LScli', 'autocomplete_bool'),
  );

  /**
   * Check an LDAP search URI value
   *
   * @param mixed $value The value to check
   * @param array $options Validation option
   * @param object $formElement The LSformElement object
   *
   * @return boolean true if the value is valid, false otherwise
   */
  public static function validate($value, $options=array(), &$formElement) {
    self :: log_trace("validate($value): options = ".varDump($options));
    $uri_parts = explode('?', $value);

    self :: log_trace("validate($value): URI parts = ".varDump($uri_parts));

    /*
     * The LDAP URI
     */
    if (!preg_match('/^(?P<proto>ldaps?)\:\/\/(?P<host>[^\/\:]+)?(:(?P<port>[0-9]+))?\/(?P<basedn>.*)$/', $uri_parts[0], $m)) {
      throw new LSformRuleException(getFData(_('Invalid LDAP server URI (%{uri})'), $uri_parts[0]));
    }
    self :: log_trace("validate($value): parsed LDAP URI:".varDump($m));

    // Check LDAP host
    if ($m['host']) {
      if (filter_var($m['host'], FILTER_VALIDATE_IP)) {
        self :: log_trace("validate($value): '".$m['host']."' is a valid IP address");
      }
      elseif (
        filter_var($m['host'], FILTER_VALIDATE_DOMAIN) &&
        (!LSconfig :: get('params.check_resolving_ldap_host', true, 'bool', $options) || @gethostbyname($m['host']) != $m['host'])
      ) {
        self :: log_trace("validate($value): '".$m['host']."' is a valid domain name");
      }
      else {
        throw new LSformRuleException(getFData(_('Invalid LDAP host (%{host})'), $m['host']));
      }

      if ($m['port'] && $m['port'] < 1 || $m['port'] > 65535) {
        throw new LSformRuleException(getFData(_('Invalid LDAP port (%{port})'), $m['port']));
      }
    }
    elseif ($m['port']) {
      throw new LSformRuleException(getFData(_('A LDAP URI could not contain port without host (%{host}:%{port})'), $m));
    }
    else {
      self :: log_trace("validate($value): URI doesn't contain LDAP host");
      if (LSconfig :: get('params.host_required', False, 'bool', $options))
        throw new LSformRuleException(_('LDAP host not provided but required'));
    }

    // Check base DN
    if (isset($m['basedn']) && $m['basedn']) {
      if (!isCompatibleDNs($m['basedn'], LSsession :: getRootDn()))
        throw new LSformRuleException(getFData(_('Invalid base DN (%{basedn})'), $m['basedn']));
      self :: log_trace("validate($value): base DN '".$m['basedn']."' is valid");
    }
    else {
      self :: log_trace("validate($value): URI doesn't contain search base DN");
      if (LSconfig :: get('params.basedn_required', False, 'bool', $options))
        throw new LSformRuleException(_('Search base DN not provided but required'));
    }

    /*
     * Attributes (optionals)
     */
    $max_attrs_count = LSconfig :: get('params.max_attrs_count', null, null, $options);
    if (isset($uri_parts[1]) && $uri_parts[1]) {
      $attrs = explode(',', $uri_parts[1]);
      if (!is_empty($max_attrs_count) && count($attrs) > $max_attrs_count)
        throw new LSformRuleException(
          getFData(
            _('Invalid searched attributes count (%{attrCount} > %{maxAttrsCount})'),
            array('attrCount' => count($attrs), 'maxAttrsCount' => $max_attrs_count)
          )
        );
      foreach($attrs as $attr) {
        if (!preg_match('/^[a-z][a-z0-9\-]+$/i', $attr)) {
          throw new LSformRuleException(getFData(_('Invalid attribute name (%{attr})'), $attr));
        }
      }
    }
    else {
      self :: log_trace("validate($value): no attribute name provided");
      if (
        LSconfig :: get('params.attr_required', False, 'bool', $options) ||
        (!is_empty($max_attrs_count) && $max_attrs_count > 0)
      )
        throw new LSformRuleException(_('Attribute name not provided but required'));
    }

    /*
     * Scope
     */
    if (isset($uri_parts[2]) && $uri_parts[2]) {
      if (!in_array($uri_parts[2], array('base', 'one', 'sub'))) {
        throw new LSformRuleException(
          getFData(
            _('Invalid search scope (%{scope}). Must be one of the following value : base, one or sub.'),
            $uri_parts[2]
          )
        );
      }
    }
    else {
      self :: log_trace("validate($value): no search scope provided");
      if (LSconfig :: get('params.scope_required', true, 'bool', $options))
        throw new LSformRuleException(_('Search scope not provided but required'));
    }

    /*
     * LDAP Filter (optinal)
     */
    if (isset($uri_parts[3]) && $uri_parts[3]) {
      /*
       Try to parse LDAP filter string to validate it

       Due to a limitation of Net_LDAP2_Filter::parse() that only
       support filter enclosed by parentheses, if string does not
       start with "(", enclose the filter with parentheses.
       */
      $filter = @Net_LDAP2_Filter::parse(
        ($uri_parts[3][0]=='('?$uri_parts[3]:"(".$uri_parts[3].")")
      );
      if (!$filter instanceof Net_LDAP2_Filter) {
        throw new LSformRuleException(getFData(_('Invalid LDAP filter ("%{filter}")'), $uri_parts[3]));
      }
      self :: log_trace("validate($value): LDAP search filter '".$uri_parts[3]."' is valid.");
    }
    else {
      self :: log_trace("validate($value): no search filter provided");
      if (LSconfig :: get('params.filter_required', false, 'bool', $options))
        throw new LSformRuleException(_('Search filter not provided but required'));
    }

    self :: log_trace("validate($value): LDAP search URI is valid.");
    return True;
  }

}
