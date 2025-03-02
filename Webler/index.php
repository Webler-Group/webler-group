<?php
session_start();

require_once __DIR__ . '/classes/UserController.php';

$userController = new UserController();

// Fetch all users who are marked as iterable
$users = $userController->getAllUsers(true);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webler Group</title>
    <?php include '../Webler/includes/css.php'; ?>
    <style>
        /* Avatar style */
        .avatar {
            width: 128px;
            height: 128px;
            border-radius: 50%; /* Make it circular */
            object-fit: cover; /* Ensure the image is cropped to fit the circle */
            border: 2px solid #ddd; /* Optional border around avatar */
            margin-bottom: 10px;
        }
        .user-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px;
            text-align: center;
            width: 200px;
            border-radius: 8px;
        }
        .user-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        .user-list .user-card {
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .user-name {
            font-weight: bold;
            margin-top: 10px;
        }
        .user-bio {
            font-size: 14px;
            color: #555;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include '../Webler/partials/header.php'; ?>

    <div class="content-wrapper">
        <?php include '../Webler/partials/navbar.php'; ?>
        <main>
            <h1>Webler Group - Home</h1>
            <div class="user-list">
                <?php foreach ($users as $user): ?>
                    <?php
                        $avatarUrl = $userController->getAvatarUrl($user);
                    ?>
                    <div class="user-card">
                        <img src="<?= $avatarUrl ?>" alt="Avatar" class="avatar">
                        <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                        <?php if (!empty($user['bio'])): ?>
                            <div class="user-bio"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <?php include '../Webler/partials/footer.php'; ?>
    <?php include '../Webler/includes/js.php'; ?>
</body>
</html>
