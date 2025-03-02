<?php
session_start();

require_once __DIR__ . '/classes/UserController.php';

$userController = new UserController();

if (!$userController->getCurrentId()) {
    header('Location: /Webler/index.php');
    exit();
}

$user = $userController->getCurrent();
$isAdmin = $user['is_admin'];

// Handle form submission for profile update
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'edit-profile':
            $name = $_POST['name'] ?? '';
            $bio = $_POST['bio'] ?? '';

            // Validate and sanitize the input if necessary

            $user['name'] = $name;
            $user['bio'] = $bio;

            if ($userController->updateUser($user)) {
                $user = $userController->getCurrent(); // Refresh user data
                $successMessage = "Profile updated successfully.";
            } else {
                $errorMessage = "Profile update failed.";
            }
            break;
        case "upload-avatar":

            $file = $_FILES["avatar"];

            // Check if file was uploaded successfully
            if ($file["error"] == UPLOAD_ERR_OK) {

                $userController->updateAvatar($user, $file);

                $successMessage = "Profile avatar uploaded successfully.";
            } else {
                $errorMessage = "Profile avatar failed to upload.";
            }
            break;
    }
}

$avatarUrl = $userController->getAvatarUrl($user);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <?php include '../Webler/includes/css.php'; ?>
    <style>
        .avatar-preview-container {
            position: relative;
            width: 128px;
            height: 128px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #ddd;
            margin-bottom: 10px;
        }

        .avatar-preview-container canvas {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <?php include '../Webler/partials/header.php'; ?>

    <div class="content-wrapper">
        <?php include '../Webler/partials/navbar.php'; ?>
        <main>
            <?php include './partials/user-navigation.php'; ?>
            <div class="user-main">
                <h1>Edit Profile</h1>
                <?php if (isset($successMessage)): ?>
                    <p style="color: green;"><?php echo $successMessage; ?></p>
                <?php elseif (isset($errorMessage)): ?>
                    <p style="color: red;"><?php echo $errorMessage; ?></p>
                <?php endif; ?>

                <!-- Profile Update Form -->
                <form method="POST">
                    <div>
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name"
                            value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="bio">Bio:</label>
                        <textarea id="bio" name="bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="action" value="edit-profile">Save Changes</button>
                </form>

                <!-- Avatar Upload Form -->
                <h2>Upload Avatar</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div>
                        <label for="avatar">Avatar:</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewAvatar(event)" required>
                    </div>

                    <!-- Avatar Preview Canvas -->
                    <div class="avatar-preview-container">
                        <canvas id="avatarCanvas" data-avatar-url="<?= $avatarUrl ?>"></canvas>
                    </div>

                    <button type="submit" name="action" value="upload-avatar">Upload Avatar</button>
                </form>
            </div>
        </main>
    </div>

    <?php include '../Webler/partials/footer.php'; ?>
    <?php include '../Webler/includes/js.php'; ?>

    <script type="text/javascript" src="/Webler/assets/js/avatar-upload.js"></script>
</body>

</html>