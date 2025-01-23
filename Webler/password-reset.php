<?php
session_start();

// Include necessary files
require_once __DIR__ . '/../Webler/classes/UserController.php';

// Initialize message variable
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get token, userId, and password details from POST request
    $token = $_POST['token'] ?? '';
    $userId = $_POST['user_id'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Create a UserController instance
    $userController = new UserController();

    // Validate token and user_id
    if ($tokenData = $userController->validateToken($token)) {

        if ($tokenData && $tokenData['user_id'] == $userId) {
            // Check if passwords match
            if ($newPassword === $confirmPassword) {
                // Hash the new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update the user's password
                $updateSuccessful = $userController->updateUser([
                    'id' => $userId,
                    'password' => $hashedPassword
                ]);

                if ($updateSuccessful) {
                    $message = "Password successfully updated. <a href='/Webler/index.php'>Go to login page</a>";
                } else {
                    $message = "Failed to update the password. Please try again.";
                }
            } else {
                $message = "Passwords do not match. Please try again.";
            }
        } else {
            $message = "Invalid token or user ID.";
        }
    } else {
        $message = "Invalid or expired token.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <?php include '../Webler/includes/css.php'; ?>
</head>

<body>
    <?php include '../Webler/partials/header.php'; ?>

    <div class="content-wrapper">
        <?php include '../Webler/partials/navbar.php'; ?>
        <main>
            <div class="password-reset-form">
                <h2>Reset Your Password</h2>

                <?php if ($message) {
                    echo "<p>$message</p>";
                } else { ?>
                    <form action="" method="post">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_GET['userId'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                        
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        
                        <button type="submit">Reset Password</button>
                    </form>
                <?php } ?>
            </div>
        </main>
    </div>

    <?php include '../Webler/partials/footer.php'; ?>
    <?php include '../Webler/includes/js.php'; ?>
</body>

</html>