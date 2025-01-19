<?php
require_once '../config.php';         
require_once 'classes/Ephemerous.php';        

// Create Ephemerous object and connect to the database
$ephemerousDb = new Ephemerous($dbDSN, $dbUser, $dbPassword);

// If the form is submitted, insert the new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $ephemerousDb->insert($_POST['message']);
}

// Fetch the latest message from the database
$latestMessage = $ephemerousDb->get();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ephemerous Message</title>
    <?php include '../Webler/includes/css.php'; ?>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <?php require '../Webler/partials/header.php'; ?>

    <div class="content-wrapper">
        <?php require '../Webler/partials/navbar.php'; ?>
        <main>
            <h1>Ephemerous Message</h1>

            <!-- Display the latest message -->
            <div class="message-box">
                <strong>Latest Message:</strong><br>
                <p><?php echo htmlspecialchars($latestMessage); ?></p>
            </div>

            <!-- Form to create a new message -->
            <div class="form-box">
                <h3>Create a New Message</h3>
                <form method="post" action="">
                    <textarea name="message" rows="4" maxlength="255" placeholder="Enter your message here" required></textarea><br>
                    <button type="submit">Submit</button>
                </form>
            </div>
        </main>
    </div>

    <?php include '../Webler/partials/footer.php'; ?>

    <?php include '../Webler/includes/js.php'; ?>
</body>
</html>
