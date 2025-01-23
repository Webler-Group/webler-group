<?php
session_start();

require_once __DIR__ . '/classes/UserController.php';
require_once __DIR__. '/classes/CsrfTokenUtil.php';

$userController = new UserController();

if (!isset($_SESSION['user_id'])) {
    header('Location: /Webler/index.php');
    exit();
}

$currentUser = $userController->get($_SESSION['user_id']);
$isAdmin = $currentUser['is_admin'];

// Ensure only admin users can access this page
if (!$isAdmin) {
    header('Location: /Webler/profile.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php CsrfTokenUtil::addTokenMetaTag(); ?>
    <title>Admin Panel</title>
    <?php include '../Webler/includes/css.php'; ?>
    <link rel="stylesheet" href="/Webler/assets/css/admin.css">
</head>

<body>
    <?php include '../Webler/partials/header.php'; ?>
    <div class="content-wrapper">
        <?php include '../Webler/partials/navbar.php'; ?>
        <main>
            <?php include './partials/user-navigation.php'; ?>
            <div class="user-main">
                <h1>Admin Panel</h1>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Is Admin?</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <tr id="row-new" style="display: none;">
                            <td>New</td>
                            <td>
                                <input autocomplete="off" type="text" class="edit-input admin-edit-input" name="name" placeholder="Name">
                            </td>
                            <td>
                                <input autocomplete="off" type="email" class="edit-input admin-edit-input" name="email" placeholder="Email" required>
                            </td>
                            <td>
                                <input type="checkbox" class="edit-input admin-edit-input" name="is_admin">
                            </td>
                            <td>
                                <div class="action-buttons admin-action-buttons">
                                    <button class="save-btn admin-save-btn" type="button" onclick="admin.saveNewUser()">Save User</button>
                                    <button class="cancel-btn admin-cancel-btn" type="button" onclick="admin.cancelNewUser()">Cancel</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button id="createUserBtn" class="admin-createUserBtn" type="button">Create User</button>
            </div>
        </main>
    </div>

    <?php include '../Webler/partials/footer.php'; ?>
    <?php include '../Webler/includes/js.php'; ?>

    <script type="text/javascript" src="/Webler/assets/js/admin.js"></script>
</body>

</html>