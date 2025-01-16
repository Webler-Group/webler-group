<?php

class Ephemerous {
    private $db;

    // Constructor to establish a database connection
    public function __construct($dbFile) {
        try {
            // Connect to the SQLite database
            $this->db = new PDO('sqlite:' . $dbFile);
            // Set error mode to exceptions
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        $this->createTable();
    }

    // Method to create the table if it doesn't exist
    public function createTable() {
        $query = "
            CREATE TABLE IF NOT EXISTS ephemerous (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                message TEXT
            );
        ";
        $this->db->exec($query);
    }

    // Method to get all records from the table
    public function get() {
        try {
            $stmt = $this->db->query("SELECT * FROM ephemerous ORDER BY id DESC LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return $result['message'];
            } else {
                return "No records found.";
            }
        } catch (PDOException $e) {
            echo "Error fetching data: " . $e->getMessage();
        }
    }

    // Method to insert a message into the table
    public function insert($message) {
        try {
            //remove whitespace
            $message = trim($message);
            //remove html tags
            $message = strip_tags($message);
            //limit to 255 characters
            if(strlen($message)>255){
                $message = substr($message, 0, 255);
            }
            $stmt = $this->db->prepare("INSERT INTO ephemerous (message) VALUES (:message)");
            $stmt->bindParam(':message', $message);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error inserting data: " . $e->getMessage();
        }
    }
}

?>
