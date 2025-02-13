<?php

unset($CFG);
global $CFG;

$CFG = new stdClass();

$CFG->appName = "Webler Group";
$CFG->appUrl = "webler.com";

$CFG->dbHost = 'localhost';
$CFG->dbName = 'webler_localhost_db';
$CFG->dbDSN = "mysql:host=$CFG->dbHost;dbname=$CFG->dbName;";
$CFG->dbUser = 'webler';
$CFG->dbPassword = 'password';

$CFG->adminEmail = 'admin@webler.com';
$CFG->adminPassword = 'Webler123.';

$CFG->emailHost = "";
$CFG->emailPort = 465;
$CFG->emailUser = "";
$CFG->emailPassword = "";

$CFG->dataroot =  "/var/www/webler-group/filesystem";

?>
