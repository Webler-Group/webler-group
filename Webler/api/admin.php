<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../classes/UserController.php';

$result = [
    'success' => true
];
$userController = new UserController($dbDSN, $dbUser, $dbPassword);

$middleware = [
    function($req) { return isset($_SESSION['user_id']); },
    function($req) use ($userController) { $req->user = $userController->get($_SESSION['user_id']); return $req->user['is_admin']; },
    function($req) use ($userController) {
        if(isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'edit-user':
                    $user = [
                        'name' => $_POST['name'],
                        'email' => $_POST['email'],
                        'is_admin' => $_POST['is_admin'],
                        'id' => $_POST['edit_user_id']
                    ];
                    $userController->updateUser($user);
                    return true;
                    break;
            }
            return false;
        }
    }
];
$req = new stdClass();

foreach($middleware as $fn) {
    if(!$fn($req)) {
        $result['success'] = false;
        break;
    }
}

echo json_encode($result);

?>