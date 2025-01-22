<?php

require_once __DIR__ . '/../../Webler/classes/Controller.php';

class UserController extends Controller
{
    protected function init() {}

    public function login($email, $password, callable $errorCallback = null)
    {
        try {
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
                if ($errorCallback) {
                    $errorCallback("Invalid email or password.");
                } else {
                    return false; // Handle as needed
                }
            }
        } catch (PDOException $e) {
            if ($errorCallback) {
                $errorCallback("Error during login: " . $e->getMessage());
            }
            return false;
        }
    }

    public function get($userId, callable $errorCallback = null)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            if ($errorCallback) {
                $errorCallback("Error fetching user: " . $e->getMessage());
            }
            return false;
        }
    }

    public function getAllUsers(callable $errorCallback = null)
    {
        try {
            $stmt = $this->db->query("SELECT * FROM users");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            if ($errorCallback) {
                $errorCallback("Error fetching all users: " . $e->getMessage());
            }
            return [];
        }
    }

    public function deleteUser($userId, callable $errorCallback = null)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            return $stmt->execute(['id' => $userId]);
        } catch (PDOException $e) {
            if ($errorCallback) {
                $errorCallback("Error deleting user: " . $e->getMessage());
            }
            return false;
        }
    }

    public function updateUser($user, callable $errorCallback = null)
    {
        try {
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
            return $stmt->execute($params);
        } catch (PDOException $e) {
            if ($errorCallback) {
                $errorCallback("Error updating user: " . $e->getMessage());
            }
            return false;
        }
    }

    public function createUser($user, callable $errorCallback = null)
    {
        try {
            // Generate a random long password
            $randomPassword = $this->generateRandomPassword();

            // Hash the password securely
            $hashedPassword = password_hash($randomPassword, PASSWORD_BCRYPT);

            // Prepare SQL statement
            $stmt = $this->db->prepare("INSERT INTO users (email, name, is_admin, password) VALUES (:email, :name, :is_admin, :password)");

            // Execute the statement with provided and generated data
            $stmt->execute([
                'email' => $user['email'],
                'name' => $user['name'] ?? '', // Use empty string if name is not provided
                'is_admin' => $user['is_admin'] ? 1 : 0,
                'password' => $hashedPassword
            ]);

            // Optionally, send the generated password to the user via email
            // $this->sendPasswordToUser($user['email'], $randomPassword);

            // Return the created user ID
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            if ($errorCallback) {
                $errorCallback("Error creating user: " . $e->getMessage());
            }
            return false;
        }
    }

    public function getUsername($user)
    {
        return isset($user['name']) ? $user['name'] : 'Weblerian';
    }

    private function generateRandomPassword($length = 16)
    {
        // Define possible characters for the password
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
        $charactersLength = strlen($characters);
        $randomPassword = '';

        // Generate a random password string from the defined characters
        for ($i = 0; $i < $length; $i++) {
            $randomPassword .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomPassword;
    }
}