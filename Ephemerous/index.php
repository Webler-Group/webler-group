<?php
session_start();

require_once __DIR__ . '/classes/EphemerousController.php';        

// Create Ephemerous object and connect to the database
$ephemerousController = new EphemerousController();

// If the form is submitted, insert the new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $ephemerousController->insert($_POST['message']);
    header('Location: /Ephemerous/index.php');
    exit();
}

// Fetch the latest messages from the database
$latestMessages = $ephemerousController->get();
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
                <strong>Latest Messages:</strong><br>
                <?php
                    foreach($latestMessages as $message)
                        echo '<p>'.htmlspecialchars($message).'</p><hr>';
                ?>
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
