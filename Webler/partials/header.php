<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../classes/AuthController.php';

$authController = new AuthController($dbDSN, $dbUser, $dbPassword);

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'logout':
            $authController->logout();
            break;
        case 'login':
            if (!empty($_POST['email']) && !empty($_POST['password'])) {
                $authController->login($_POST['email'], $_POST['password']);
            }
            break;
    }
}
?>
<header>
    <div class="logo-wrapper">
        <img src="/Webler/assets/images/logo.png" alt="Webler Logo" class="logo">
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="post">
            <a href="/Webler/user.php">Profile</a>
            <button type="submit" name="action" value="logout">Logout</button>
        </form>
    <?php else: ?>
        <form class="login-form" method="post">
            <div class="form-row">
                <input name="email" type="email" placeholder="email" autocomplete="off">
                <input name="password" type="password" placeholder="password" autocomplete="off">
                <button type="submit" name="action" value="login">Login</button>
            </div>
            <div class="form-row">
                <a class="forgotten-password" href="#">Forgotten password?</a>
            </div>
        </form>
    <?php endif; ?>
</header>