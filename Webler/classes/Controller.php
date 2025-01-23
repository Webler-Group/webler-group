<?php

require_once __DIR__ . '/../../config.php';

abstract class Controller {
    protected $db;

    // Constructor to establish a database connection
    public function __construct() {
        global $CFG;
        try {
            // Connect to the SQLite database
            $this->db = new PDO($CFG->dbDSN , $CFG->dbUser, $CFG->dbPassword);
            // Set error mode to exceptions
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->init();
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    protected abstract function init();
}

?>