<?php

require_once __DIR__ . '/../../Webler/classes/Controller.php';

class UserController extends Controller
{
    protected function init() {}

    public function login($email, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Start a session and set the user session data
            session_start();
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_id'] = $user['id'];

            // Redirect to profile.php
            header('Location: /Webler/profile.php');
            exit();
        } else {
            echo "Invalid email or password.";
        }
    }

    public function get($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers()
    {
        $stmt = $this->db->query("SELECT * FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header('Location: /Webler/index.php');
        exit();
    }

    public function updateUser($user)
    {
        // Start building the SQL update query
        $query = "UPDATE users SET ";
        $params = [];

        // Loop through all keys in the user array
        foreach ($user as $key => $value) {
            // Skip the 'id' key as we don't want to modify that
            if ($key !== 'id') {
                $query .= "$key = :$key, ";  // Add the field to update
                $params[$key] = $value;      // Bind the parameter
            }
        }

        // Remove the trailing comma from the query
        $query = rtrim($query, ', ');
        $query .= " WHERE id = :id";
        $params['id'] = $user['id']; // Add the ID to the parameters

        // Prepare and execute the SQL statement
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
    }

    public function getUsername($user)
    {
        return isset($user['name']) ? $user['name'] : 'Weblerian';
    }
}
