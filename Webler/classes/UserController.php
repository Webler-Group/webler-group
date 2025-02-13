<?php

require_once __DIR__ . '/../../Webler/classes/Controller.php';
require_once __DIR__ . '/Database.php';

class UserController extends Controller
{
    public const SESSION_USER_ID = "user_id";

    public function login($email, $password, callable $errorCallback = null)
    {
        global $DB;
        try {
            $user = $DB->select_one('users', '*', ['email' => $email]);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_email'] = $user['email'];
                $_SESSION[self::SESSION_USER_ID] = $user['id'];

                // Redirect to profile.php
                header('Location: /Webler/profile.php');
                exit();
            } else {
                if ($errorCallback) {
                    $errorCallback("Invalid email or password.");
                }
            }
        } catch (PDOException $e) {
            if ($errorCallback) {
                $errorCallback("Error during login: " . $e->getMessage());
            }
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header('Location: /Webler/index.php');
        exit();
    }

    public function get($userId, callable $errorCallback = null)
    {
        global $DB;
        try {
            return $DB->select_one('users', '*', ['id' => $userId]);
        } catch (PDOException $e) {
            if ($errorCallback) {
                $errorCallback("Error fetching user: " . $e->getMessage());
            }
            return false;
        }
    }

    public function getByFilter(array $filter, callable $errorCallback = null)
    {
        global $DB;
        try {
            return $DB->select_one('users', '*', $filter);
        } catch (PDOException $e) {
            if ($errorCallback) {
                $errorCallback("Error fetching user: " . $e->getMessage());
            }
            return false;
        }
    }

    public function getAllUsers(callable $errorCallback = null)
    {
        global $DB;
        try {
            return $DB->select_many('users', 'id, name, email, is_admin');
        } catch (PDOException $e) {
            if ($errorCallback) {
                $errorCallback("Error fetching all users: " . $e->getMessage());
            }
            return [];
        }
    }

    public function deleteUser($userId, callable $errorCallback = null)
    {
        global $DB;
        try {
            return $DB->delete('users', ['id' => $userId]);
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

    public function createOrGetToken($userId, $tokenType, $timeAlive, $singleUse)
    {
        $this->clearExpiredTokens();
        try {
            $token = $this->findValidToken($userId, $tokenType);
            if ($token) {
                return $token;
            }

            // Calculate expiration date for the new token
            $expireDate = (new DateTime())->add(new DateInterval('PT' . $timeAlive . 'S'))->format('Y-m-d H:i:s');
            $tokenValue = base64_encode(random_bytes(32)); // Generate a random base64 token

            // Insert the new token into the database
            $stmt = $this->db->prepare("INSERT INTO tokens (user_id, token_type, value, expire_date, is_single_use) VALUES (:user_id, :token_type, :value, :expire_date, :is_single_use)");
            $stmt->execute([
                'user_id' => $userId,
                'token_type' => $tokenType,
                'value' => $tokenValue,
                'expire_date' => $expireDate,
                'is_single_use' => $singleUse
            ]);

            // Get the ID of the newly created token
            $newTokenId = $this->db->lastInsertId();

            // Retrieve the creation time of the new token
            $stmt = $this->db->prepare("SELECT expire_date FROM tokens WHERE id = :id");
            $stmt->execute(['id' => $newTokenId]);
            $newTokenData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($newTokenData) {
                $newTokenExpireDate = $newTokenData['expire_date'];

                // Delete older tokens of the same type for this user
                $deleteStmt = $this->db->prepare("DELETE FROM tokens WHERE user_id = :user_id AND token_type = :token_type AND expire_date < :expire_date");
                $deleteStmt->execute([
                    'user_id' => $userId,
                    'token_type' => $tokenType,
                    'expire_date' => $newTokenExpireDate
                ]);
            }

            return $this->getTokenById($newTokenId); // Return the generated token ID

        } catch (PDOException $e) {
            // Handle error
            return false;
        }
    }

    public function findValidToken($userId, $tokenType)
    {
        try {
            // Prepare the SQL query to find a valid, non-expired token
            $stmt = $this->db->prepare("
                SELECT * FROM tokens
                WHERE user_id = :user_id
                AND token_type = :token_type
            ");

            // Execute the query with the provided userId and tokenType
            $stmt->execute(['user_id' => $userId, 'token_type' => $tokenType]);

            // Fetch the token from the result set
            $token = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($token && new DateTime($token['expire_date']) > new DateTime()) {
                return $token;
            }

            // Return the token if found, otherwise return false
            return false;
        } catch (PDOException $e) {
            // Handle any exceptions or errors
            return false;
        }
    }

    public function getTokenById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM tokens WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle error
            return false;
        }
    }

    public function clearExpiredTokens()
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM tokens WHERE expire_date < NOW()");
            $stmt->execute();
            return $stmt->rowCount(); // Return number of deleted rows
        } catch (PDOException $e) {
            // Handle error
            return false;
        }
    }

    public function validateToken($tokenValue, $tokenType)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM tokens WHERE value = :value AND token_type = :token_type");
            $stmt->execute(['value' => $tokenValue, 'token_type' => $tokenType]);
            $token = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($token && new DateTime($token['expire_date']) > new DateTime()) {
                // Token is valid, delete it and return true
                if ($token['is_single_use']) {
                    $deleteStmt = $this->db->prepare("DELETE FROM tokens WHERE id = :id");
                    $deleteStmt->execute(['id' => $token['id']]);
                }
                return $token;
            }

            return false; // Token is invalid
        } catch (PDOException $e) {
            // Handle error
            return false;
        }
    }

    public function getCurrentId(): ?int {
        return $_SESSION[self::SESSION_USER_ID] ?? null;
    }

    public function getCurrent($errorCallback = null) {
        return $this->get($_SESSION[self::SESSION_USER_ID], $errorCallback);
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
