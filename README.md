# webler-group

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

### Apache

On Ubuntu Apache2 should be installed already or you can install it via apt.

Restart the Apache service:

```
sudo systemctl restart apache2
```

Check its status:

```
systemctl status apache2
```

You should see green text saying enabled, active (running).

### MariaDB

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

## TODO

- [x] Create config.php
- [ ] Create Header as part of Webler feature
- [ ] Create Footer as part of WEbler feature
- [ ] Document Ubuntu installation
- [ ] Document Windows installation
