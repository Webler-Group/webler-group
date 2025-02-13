# webler-group

## TODO

- [x] Create config.php
- [x] Add Header
- [x] Add Footer
- [x] Add profile page
- [x] Add admin panel
- [ ] Integrate email seding
- [x] Document Ubuntu installation
- [ ] Document Windows installation

## First run

1. Clone the project.
2. Install dependencies with composer.
3. Create config.php from config-local.php and fill in credentials.
4. Run install.php cli script.

## Structure

```
Feature1/
|--index.php   Entry point
|--page1.php
|--page2.php
|...
|--api/   Api endpoints (AJAX requests)
|--classes/   Utils used in pages
|  |--Class1.php
|  |--Class2.php
|  ...
|--partials/   header, footer, ...
|  |--header.php
|  |...
|--assets/
   |--css/
   |--js/
   |--images/
   |...
...
config.php   Configuration for DB connection, ...
```

When working on a feature switch to the feature branch!

## PHP page template

```
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webler Group</title>
    <?php include '../Webler/includes/css.php'; ?>
</head>
<body>
    <?php include '../Webler/partials/header.php'; ?>

    <div class="content-wrapper">
        <?php include '../Webler/partials/navbar.php'; ?>
        <main>
            <!-- content goes here -->
        </main>
    </div>

    <?php include '../Webler/partials/footer.php'; ?>

    <?php include '../Webler/includes/js.php'; ?>
</body>
</html>
```

## CLI

CLI scripts are located in /Webler/cli.

- install.php: Create required tables and seed default data.

```
php ./Webler/cli/install.php
```

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

#### Comoser

Install [composer](https://getcomposer.org/)

To install dependencies, open terminal in project dir and run:

```
composer update
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

Ensure that the mod_rewrite and mod_headers are enabled:

```
sudo a2enmod rewrite
sudo a2enmod headers
```

Restart the apache service.

**Create virtual host for Webler**

Clone the project from github under /var/www which is default apache directory.

Create configuration file:

```
sudo nano /etc/apache2/sites-available/webler.com.conf
```

Add the following (update the paths according to your location of the project):

```
<VirtualHost *:80>
   ServerAdmin webmaster@webler.com
   ServerName webler.com
   ServerAlias www.webler.com
   DocumentRoot /var/www/webler-group

   <Directory /var/www/webler-group>
      AllowOverride All
      Require all granted
   </Directory>


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

If you still see the default apache site in the browser. Check /etc/apache2/sites-enabled and disable the default site and all other sites except for webler:

```
sudo a2dissite 000-default.conf
```

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
MariaDB> GRANT ALL PRIVILEGES ON webler_localhost_db.* TO 'webler'@'localhost';
MariaDB> FLUSH PRIVILEGES;
