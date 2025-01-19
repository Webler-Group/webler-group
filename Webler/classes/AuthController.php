<?php

require_once __DIR__ . '/../../Webler/classes/Controller.php';

class AuthController extends Controller
{
    protected function createTable() {}

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
            header('Location: /Webler/user.php');
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

    public function logout()
    {
        session_unset();
        session_destroy();
        header('Location: /Webler/index.php');
        exit();
    }
}
