<?php

require_once __DIR__ . '/../../Webler/classes/Controller.php';
require_once __DIR__ . '/../../Webler/classes/Database.php';

class EphemerousController extends Controller
{

    // Method to get all records from the table
    public function get()
    {
        global $DB;
        try {
            $result = $DB->select_many('ephemerous', '*', [], 'id DESC', 0, 1);

            if (count($result) > 0) {
                return $result[0]['message'];
            } else {
                return "No records found.";
            }
        } catch (PDOException $e) {
            echo "Error fetching data: " . $e->getMessage();
        }
    }

    // Method to insert a message into the table
    public function insert($message)
    {
        try {
            //remove whitespace
            $message = trim($message);
            //remove html tags
            $message = strip_tags($message);
            //limit to 255 characters
            if (strlen($message) > 255) {
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
