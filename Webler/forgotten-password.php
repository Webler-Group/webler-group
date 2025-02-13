<?php
session_start();

// Include necessary files
require_once __DIR__ . '/classes/UserController.php';
require_once __DIR__ . '/classes/EmailService.php';
require_once __DIR__ . '/classes/TokenTypeEnum.php';

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userController = new UserController();

    // Get the email from the form
    $email = $_POST['email'];

    // Try to find the user by email
    $user = $userController->getByFilter(['email' => $email], function ($message) use (&$error) {
        $error = $message;
    });

    if ($user) {

        $token = $userController->createOrGetToken($user['id'], TokenTypeEnum::PASSWORD_RESET_TOKEN, 1800, true);

        if ($token) {
            $tokenValue = $token['value'];

            // Prepare encoded token and user ID for use in the URL
            $encodedToken = urlencode($tokenValue);
            $encodedUserId = urlencode($user['id']);

            // Construct the email reset link with encoded parameters
            $resetLink = "{$CFG->appUrl}/Webler/password-reset.php?token={$encodedToken}&userId={$encodedUserId}";
            $emailSubject = "Password Reset Request";
            $emailBody = "You requested a password reset. Click here to reset your password: <a href='{$resetLink}'>Reset Password</a>";

            // Initialize email service and send the email
            $emailService = new EmailService(function ($message) use (&$error) {
                echo $message;
                $error = $message;
            });
            if (!$emailService->sendEmail($email, $emailSubject, $emailBody)) {
                $message = $error ?? "Email could not be sent.";
            } else {
                // Success message
                $message = "An email with the link to reset the password has been sent to your email address.";
            }
        } else {
            // Handle token creation failure
            $message = "Error generating reset token. Please try again later.";
        }
    } else {
        // User with given email not found
        $message = $error ?? "No account associated with this email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgotten Password</title>
    <?php include '../Webler/includes/css.php'; ?>
</head>

<body>
    <?php include '../Webler/partials/header.php'; ?>

    <div class="content-wrapper">
        <?php include '../Webler/partials/navbar.php'; ?>
        <main>
            <div class="forgotten-password-form">
                <h2>Forgotten Password</h2>

                <?php
                if (isset($message)) {
                    echo "<p>$message</p>";
                } else {
                ?>
                    <form action="" method="post">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                        <button type="submit">Submit</button>
                    </form>
                <?php
                }
                ?>
            </div>
        </main>
    </div>

    <?php include '../Webler/partials/footer.php'; ?>
    <?php include '../Webler/includes/js.php'; ?>
</body>

</html>