# webler-group

## TODO

- [x] Create config.php
- [ ] Create Header as part of Webler feature
- [ ] Create Footer as part of Webler feature
- [x] Document Ubuntu installation
- [ ] Document Windows installation

## Structure

```
Feature1/
|--index.php   Entry point
|--page1.php
|--page2.php
...
|--classes/   Utils used in pages
   |--class1.php
   |--class2.php
   ...
|--partials/   Header, Footer, ...
   |--partial.php
...
config.php   Configuration for DB connection, ...
```

When working on a feature switch to the feature branch!

## Installation

### Ubuntu

#### PHP

PHP 8.2+ is required for this project. This will install newest php version:

```
sudo apt update
sudo apt install php
```

Then check the version:

```
php --version
```

#### Apache

On Ubuntu Apache2 should be installed already or you can install it:

```
sudo apt update
sudo apt install apache2
```

Restart the Apache service:

```
sudo systemctl restart apache2
```

Check its status:

```
systemctl status apache2
```

You should see green text saying enabled, active (running).

For PHP support install php module:

```
sudo apt update
sudo apt install libapache2-mod-php
```

First, verify if the files /etc/apache2/mods-enabled/php8.*.conf and /etc/apache2/mods-enabled/php8.*.load exist. If they do not exist, you can enable the module using the a2enmod command.

Then restart the apache service.

**Create virtual host for Webler**

Clone the project from github under /var/www which is default apache directory.

Craete configuration file:

```
sudo nano /etc/apache2/sites-available/webler.com.conf
```

Add the following and set the DocumentRoot based on your location of the project:

```
<VirtualHost *:80>
  ServerAdmin webmaster@webler.com
  ServerName webler.com
  ServerAlias www.webler.com
  DocumentRoot /path/to/project
  ErrorLog ${APACHE_LOG_DIR}/webler.com_error.log
  CustomLog ${APACHE_LOG_DIR}/webler.com_access.log combined
</VirtualHost>
```

Enable the virtual host:

```
sudo a2ensite webler.com.conf
sudo systemctl restart apache2
```

Edit your /etc/hosts to point the domain to your ip address:

```
sudo nano /etc/hosts
```

Add the following:

```
127.0.0.1 webler.com
```

TODO

#### MariaDB

Install:

```
sudo apt update
sudo apt install mariadb-server
```

Enable and start the service:

```
sudo systemctl enable mariadb
sudo systemctl start mariadb
```

Check its status:

```
systemctl status mariadb
```

You should see green text saying enabled, active (running).

MariaDB comes without password with the default root user, as a result only the privileged user can access mariadb. Secure mariadb with root password and remove anonymous users:

```
sudo mysql_secure_installation
```

`Enter current password for root (enter for none):` Press Enter

`Switch to unix_socket authentication [Y/n]` Press n

`Change the root password? [Y/n] ` Press Y and enter new root password

`Remove anonymous users? [Y/n]` Press Y

`Disallow root login remotely? [Y/n]` Press Y

`Remove test database and access to it? [Y/n]` Press Y

`Reload privilege tables now? [Y/n]` Press Y

Now you can access mariadb from command line:

```
mariadb -u root -p
```

Create Webler database:

```
MariaDB> CREATE DATABASE webler_localhost_db;
MariaDB> SHOW DATABASES;
```

Create Webler user and grant privileges on the newly created database to the user:

```
MariaDB> CREATE USER 'webler'@'localhost' IDENTIFIED BY 'password';
MariaDB> GRANT ALL PRIVILEGES ON webler_locahost_db.* TO 'webler'@'localhost';
MariaDB> FLUSH PRIVILEGES;
