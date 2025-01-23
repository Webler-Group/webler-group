<?php
require_once __DIR__ . '/../classes/UserController.php';

$UserController = new UserController();

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'logout':
            $UserController->logout();
            break;
        case 'login':
            if (!empty($_POST['email']) && !empty($_POST['password'])) {
                $UserController->login($_POST['email'], $_POST['password'], function($message) { echo $message; });
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
            <a href="/Webler/profile.php">Profile</a>
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
                <a class="forgotten-password" href="/Webler/forgotten-password.php">Forgotten password?</a>
            </div>
        </form>
    <?php endif; ?>
</header>