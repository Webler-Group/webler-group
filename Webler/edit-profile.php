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

// Handle form submission
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'edit-profile':
            $name = $_POST['name'] ?? '';
            $bio = $_POST['bio'] ?? '';

            // Validate and sanitize the input if necessary

            $user['name'] = $name;
            $user['bio'] = $bio;

            if($userController->updateUser($user)) {
                $user = $userController->get($_SESSION['user_id']); // Refresh user data
                $successMessage = "Profile updated successfully.";
            } else {
                $errorMessage = "Profile update failed.";
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <?php include '../Webler/includes/css.php'; ?>
</head>

<body>
    <?php include '../Webler/partials/header.php'; ?>

    <div class="content-wrapper">
        <?php include '../Webler/partials/navbar.php'; ?>
        <main>
            <?php include './partials/user-navigation.php'; ?>
            <div class="user-main">
                <h1>Edit Profile</h1>
                <?php if (isset($successMessage)) : ?>
                    <p style="color: green;"><?php echo $successMessage; ?></p>
                <?php elseif(isset($errorMessage)): ?>
                    <p style="color: red;"><?php echo $errorMessage; ?></p>
                <?php endif; ?>
                <form action="edit-profile.php" method="POST">
                    <div>
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="bio">Bio:</label>
                        <textarea id="bio" name="bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="action" value="edit-profile">Save Changes</button>
                </form>
            </div>
        </main>
    </div>

    <?php include '../Webler/partials/footer.php'; ?>
    <?php include '../Webler/includes/js.php'; ?>
</body>

</html>