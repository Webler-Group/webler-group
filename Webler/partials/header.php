<?php
require_once __DIR__ . '/../classes/UserController.php';

$userController = new UserController();

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'logout':
            $userController->logout();
            break;
        case 'login':
            if (!empty($_POST['email']) && !empty($_POST['password'])) {
                $userController->login($_POST['email'], $_POST['password'], function ($message) {
                    echo $message;
                });
            }
            break;
    }
}

$isLogged = $userController->getCurrentId();
?>
<header>
    <div class="logo-wrapper">
        <img src="/Webler/assets/images/logo.png" alt="Webler Logo" class="logo">
    </div>

    <?php if ($isLogged): ?>
        <?php
        $user = $userController->getCurrent();
        ?>
        <div style="display: flex; align-items: center; gap: 10px;">
            <div>
                Logged as <?php echo htmlspecialchars($userController->getUsername($user), ENT_QUOTES, 'UTF-8'); ?>
                <br>
                <a href="/Webler/profile.php">Go to profile</a>
            </div>
            <form method="post">
                <button type="submit" name="action" value="logout">Logout</button>
            </form>
        </div>
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