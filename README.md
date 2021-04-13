[![LdapSaisie](src/images/default/logo.png)](https://ldapsaisie.org)

LdapSaisie is a Web application developed to manage LDAP directory. It has been written in PHP / JavaScript and is published under the GNU GPL license. This application is designed to abstract the complexity of the directory through a simple and intuitive administration interface. It was designed with the objective of maximal modularity and easy extension or adaptation: you can use modules, extensions and plugins. LdapSaisie allows any system administrator to manage data stored inside a LDAP server, and thus administrate its information system in a simple manner. It's also possible to let users access their own data, with read-only or read-write access.

__[Project website](https://ldapsaisie.org)__ | __[Documentation](https://ldapsaisie.org/doc/full)__ | __[Demonstration](https://ldapsaisie.org/demo)__

## Key features

* Management of one or several directories;
* Management of simple and multi-branch directories;
* Able to manage unlimited objects types;
* Allow unlimited users to connect to the interface;
* A smooth rights management allows to manage each object and attributes access rights, and allows to delegate rights;
* Able to manage a lot of attribute type. Each attribute is given specific features which ease application navigation (Automatic password generation, field auto-completion);
* Able to manage high rate of attributes verification rules;
* Easy management of objects relationships;
* Easy modification of application design using templates;
* Management of personalized triggers, which allows to execute you own scripts, functions or methods at any action. The triggers scope is set to able a whole modification of application behavior;
* Smooth attributes visualization management, allowing to auto-modify attributes according to others;
* Possibility to manage hidden attributes.
* Customizable responsive web interface

## Screenshots

Please see [project website](https://ldapsaisie.org/en:screenshot) for some screenshots of the web interface. Keep in mind that the web interface could be personalized to feet with your needs.

## Demonstration

A demonstration version is available at :

http://ldapsaisie.org/demo

This demonstration interface is connected to a sample LDAP directory such that the schema and the imported LDIF file are provided with the sources of the application (in the `lsexample` directory).

Several demonstration accounts exist and have more or less extensive rights. These different accounts are presented on the login page.

## Installation

### Requirements

* PHP (>= 5.6)
* PEAR [NetLDAP2](http://pear.php.net/package/Net_LDAP2) package
* [Smarty](http://www.smarty.net/) package

Some others dependencies exists for specific features, please see [documentation](https://ldapsaisie.org/doc/all-in-one/LdapSaisie.html#install-requirements) for details (french).

### Download

#### Using Debian packages

You can easily install LdapSaisie using Debian packages by using the following command to configure the project's repository :
```
echo "deb http://ldapsaisie.org/debian buster main" | sudo tee /etc/apt/sources.list.d/ldapsaisie.list
wget -O - http://ldapsaisie.org/debian/ldapsaisie.gpg.key | sudo apt-key add -
apt-get update
apt-get install ldapsaisie ldapsaisie-archive-keyring
```

#### Using Git

The Git repos can be cloned anonymously with the command :

```
git clone https://gitlab.easter-eggs.com/ee/ldapsaisie.git
```

#### Snapshot

Every 15 minutes, a snapshot of the Git repository is built and available for download here:

http://ldapsaisie.org/download/ldapsaisie-snapshoot.tar.gz

### Configuration

It's the main step of the installation process and the most complicated. Firstly, you have to configure the global configuration mostly contain in the file `conf/config.inc.php`. Second, you will have to configure your object types and their relationships. You could refer to example files provided with the application and to the [official documentation](https://ldapsaisie.org/doc/all-in-one/LdapSaisie.html#config-LSobject) (in french).

__Note:__ A more details [tutorial](https://ldapsaisie.org/doc/all-in-one/LdapSaisie.html#install-tutorial) (in french) is also provided in official documentation.

## Upgrade

### Using Debian packages

When using Debian packages, the upgrading process to pretty simple: just upgrade the package:

```
apt update
apt install ldapsaisie
```

Once the application has been updated, paid attention to new features and points of vigilance described in [official documentation](https://ldapsaisie.org/doc/all-in-one/LdapSaisie.html#upgrade) (in french).

### Using Git

When using git, you could use the `upgradeFromGit.sh` which automates the update if you have followed the installation procedure for it.

This script will then take care of:

* Clean the working-tree Git from the symbolic links of the local files (and possibly the theme) set up during a previous execution;
* Empty the template cache;
* Update the Git working-tree via a `git pull`;
* Install symbolic links for local files. In case of locally modified files, the script will notify you and will allow you to simply update your local file (via a `vim -d`);
* Detect changes in the MO files (translation) and in this case trigger a reload of the web server to be taken into account;
* Option: to compile an up-to-date local version of the documentation;

Once the application has been updated, paid attention to new features and points of vigilance described in [official documentation](https://ldapsaisie.org/doc/all-in-one/LdapSaisie.html#upgrade) (in french).

## License

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program (see `LICENSE` file); if not, write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
