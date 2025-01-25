<?php
require_once __DIR__ . '/classes/Controller.php';


// Initialize controller
$controller = new MultiplayerController();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationName = $_POST['application_name'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        // Attempt to create application
        $createdAppName = $controller->createApplication($applicationName, $password);
        $successMessage = "Application '$createdAppName' created successfully!";
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Multiplayer Application</title>
</head>
<body>
    <?php if (isset($errorMessage)): ?>
        <div style="color: red;"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>
    
    <?php if (isset($successMessage)): ?>
        <div style="color: green;"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <label>Application Name:
            <input type="text" name="application_name" required>
        </label>
        <br>
        <label>Password:
            <input type="password" name="password" required>
        </label>
        <br>
        <input type="submit" value="Create Application">
    </form>
</body>
</html>