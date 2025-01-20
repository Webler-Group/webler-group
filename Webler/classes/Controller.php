<?php

abstract class Controller {
    protected $db;

    // Constructor to establish a database connection
    public function __construct($dbDSN, $dbUser, $dbPassword) {
        try {
            // Connect to the SQLite database
            $this->db = new PDO($dbDSN , $dbUser, $dbPassword);
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