<?php
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/classes/AuthController.php';

$authController = new AuthController($dbDSN, $dbUser, $dbPassword);

if (!isset($_SESSION['user_id'])) {
    header('Location: /Webler/login.php');
    exit();
}

$user = $authController->get($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User</title>
    <?php include '../Webler/includes/css.php'; ?>
</head>

<body>
    <?php include '../Webler/partials/header.php'; ?>

    <div class="content-wrapper">
        <?php include '../Webler/partials/navbar.php'; ?>
        <main>
            <div class="user-main">
                <div class="user-info">
                    <h1>Welcome, <?php echo htmlspecialchars($user['email']); ?>!</h1>
                    <p>User ID: <?php echo htmlspecialchars($user['id']); ?></p>
                </div>
            </div>
        </main>
    </div>

    <?php include '../Webler/partials/footer.php'; ?>
    <?php include '../Webler/includes/js.php'; ?>
</body>

</html>