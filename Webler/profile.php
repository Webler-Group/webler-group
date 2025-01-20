<?php
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/classes/UserController.php';

$userController = new UserController($dbDSN, $dbUser, $dbPassword);

if (!isset($_SESSION['user_id'])) {
    header('Location: /Webler/index.php');
    exit();
}

$user = $userController->get($_SESSION['user_id']);
$isAdmin = $user['is_admin'];

$username = $userController->getUsername($user);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <?php include '../Webler/includes/css.php'; ?>
</head>

<body>
    <?php include '../Webler/partials/header.php'; ?>

    <div class="content-wrapper">
        <?php include '../Webler/partials/navbar.php'; ?>
        <main>
            <?php include './partials/user-navigation.php'; ?>
            <div class="user-main">
                <div class="user-info">
                    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
                    <p>User ID: <?php echo htmlspecialchars($user['id']); ?></p>
                    <?php if (!empty($user['bio'])): ?>
                        <p>Bio: <?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <?php include '../Webler/partials/footer.php'; ?>
    <?php include '../Webler/includes/js.php'; ?>
</body>

</html>