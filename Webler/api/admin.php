<?php
session_start();

require_once __DIR__ . '/../classes/UserController.php';

$result = [
    'success' => true
];
$userController = new UserController();

$middleware = [
    function ($req) {
        return isset($_SESSION['user_id']);
    },
    function ($req) use ($userController) {
        $req->user = $userController->get($_SESSION['user_id']);
        return $req->user['is_admin'];
    },
    function ($req) use ($userController) {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'edit-user':
                    $user = [
                        'name' => $_POST['name'],
                        'email' => $_POST['email'],
                        'is_admin' => $_POST['is_admin'],
                        'id' => $_POST['edit_user_id']
                    ];
                    if (!$userController->updateUser($user)) {
                        return false;
                    }
                    $req->data = $userController->get($user['id']);
                    return true;
                case 'create-user':
                    $user = [
                        'name' => $_POST['name'],
                        'email' => $_POST['email'],
                        'is_admin' => $_POST['is_admin'],
                        'id' => $_POST['create_user_id']
                    ];
                    $newUserId = $userController->createUser($user);
                    if (!$newUserId) {
                        return false;
                    }
                    $req->data = $userController->get($newUserId);
                    return true;
                case 'delete-user':
                    return $userController->deleteUser($_POST['delete_user_id']);
                case 'get-users':
                    $req->data = $userController->getAllUsers();
                    return true;
            }
            return false;
        }
    }
];
$req = new stdClass();

foreach ($middleware as $fn) {
    if (!$fn($req)) {
        $result['success'] = false;
        break;
    }
}

if (isset($req->data)) {
    $result['data'] = $req->data;
}

echo json_encode($result);
