<?php
session_start();

require_once __DIR__ . '/../classes/ApiRequest.php';
require_once __DIR__ . '/../classes/UserController.php';
require_once __DIR__ . '/../classes/CsrfTokenUtil.php';

$userController = new UserController();

$middleware = [
    function ($req) use ($userController) {
        if (!$userController->getCurrentId()) {
            $req->message = 'User not logged in.';
            return false;
        }
        return true;
    },
    function ($req) use ($userController) {
        $req->user = $userController->getCurrent();
        if (!$req->user['is_admin']) {
            $req->message = 'User does not have admin privileges.';
            return false;
        }
        return true;
    },
    function ($req) {
        $csrfToken = $_POST['csrf_token'] ?? ''; // Or $_SERVER['HTTP_CSRF_TOKEN'] for headers
        if (!CsrfTokenUtil::validateToken($csrfToken)) {
            $req->message = 'Invalid CSRF token.';
            return false;
        }
        return true;
    },
    function ($req) use ($userController) {
        if (!isset($_POST['action'])) {
            $req->message = 'No action specified.';
            return false;
        }

        $action = $_POST['action'];
        $fieldMap = [
            'edit-user' => ['edit_user_id', 'name', 'email'],
            'create-user' => ['name', 'email'],
            'delete-user' => ['delete_user_id'],
            'get-users' => [],
            'change-password' => ['change_password_user_id', 'new_password']
        ];

        if (!ApiRequest::validatePostFields($fieldMap[$action] ?? [])) {
            $req->message = 'Missing or empty required fields for action: ' . $action;
            return false;
        }

        switch ($action) {
            case 'edit-user':
                $user = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'is_admin' => $_POST['is_admin'],
                    "is_iterable" => $_POST["is_iterable"],
                    'id' => $_POST['edit_user_id']
                ];
                if (!$userController->updateUser($user)) {
                    $req->message = 'Failed to update user.';
                    return false;
                }
                $req->data = $userController->get($user['id']);
                return true;

            case 'create-user':
                $user = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'is_admin' => $_POST['is_admin'],
                    "is_iterable" => $_POST["is_iterable"]
                ];
                $newUserId = $userController->createUser($user);
                if (!$newUserId) {
                    $req->message = 'Failed to create user.';
                    return false;
                }
                $req->data = $userController->get($newUserId);
                return true;

            case 'delete-user':
                if (!$userController->deleteUser($_POST['delete_user_id'])) {
                    $req->message = 'Failed to delete user.';
                    return false;
                }
                return true;

            case 'get-users':
                $req->data = $userController->getAllUsers();
                return true;

            case 'change-password':
                $user = [
                    'password' => password_hash($_POST['new_password'], PASSWORD_BCRYPT),
                    'id' => $_POST['change_password_user_id']
                ];
                if (!$userController->updateUser($user)) {
                    $req->message = 'Failed to change password.';
                    return false;
                }
                return true;

            default:
                $req->message = 'Invalid action.';
                return false;
        }
    }
];

$apiRequest = new ApiRequest($middleware);

$result = $apiRequest->execute();
echo json_encode($result);