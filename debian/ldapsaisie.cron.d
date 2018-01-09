# Purge old temporary files (if not remove by LdapSaisie on logout)
10 1 * * * www-data find /var/cache/ldapsaisie/ -type f -name '*.tmp' -mtime +2 -delete
