<?php
require_once 'configs/config.php';         
require_once 'classes/Ephemerous.php';         

// Create Ephemerous object and connect to the database
$ephemerousDb = new Ephemerous($dbFile);

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
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #333;
        }
        .message-box {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            word-wrap: break-word;
        }
        .form-box {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            max-width:100%;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <h1>Ephemerous Message</h1>

    <!-- Display the latest message -->
    <div class="message-box">
        <strong>Latest Message:</strong><br>
        <p><?php echo htmlspecialchars($latestMessage); ?>
        </p>
    </div>

    <!-- Form to create a new message -->
    <div class="form-box">
        <h3>Create a New Message</h3>
        <form method="post" action="">
            <textarea name="message" rows="4" maxlength="255" placeholder="Enter your message here" required></textarea><br>
            <button type="submit">Submit</button>
        </form>
    </div>

</body>
</html>
