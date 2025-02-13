<?php

require_once __DIR__ . '/Database.php';

abstract class Controller {
    protected $db;

    // Constructor to establish a database connection
    public function __construct() {
        global $DB;
        $this->db = $DB->getConnection();
    }
}

?>