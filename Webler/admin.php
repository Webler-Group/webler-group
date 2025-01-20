<?php
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/classes/UserController.php';

$userController = new UserController($dbDSN, $dbUser, $dbPassword);

if (!isset($_SESSION['user_id'])) {
    header('Location: /Webler/index.php');
    exit();
}

$currentUser = $userController->get($_SESSION['user_id']);
$isAdmin = $currentUser['is_admin'];

// Ensure only admin users can access this page
if (!$isAdmin) {
    header('Location: /Webler/profile.php');
    exit();
}

$users = $userController->getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <?php include '../Webler/includes/css.php'; ?>
</head>
<body>
    <?php include '../Webler/partials/header.php'; ?>
    <div class="content-wrapper">
        <?php include '../Webler/partials/navbar.php'; ?>
        <main>
            <?php include './partials/user-navigation.php'; ?>
            <div class="user-main">
                <h1>Admin Panel</h1>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Is Admin?</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $oneUser) : ?>
                            <tr id="row-<?php echo $oneUser['id']; ?>">
                                <td><?php echo htmlspecialchars($oneUser['id']); ?></td>
                                <td>
                                    <span class="static-span"><?php echo htmlspecialchars($userController->getUsername($oneUser)); ?></span>
                                    <input autocomplete="off" type="text" class="edit-input" style="display:none;" name="name" value="<?php echo htmlspecialchars($oneUser['name']); ?>">
                                </td>
                                <td>
                                    <span class="static-span"><?php echo htmlspecialchars($oneUser['email']); ?></span>
                                    <input autocomplete="off" type="email" class="edit-input" style="display:none;" name="email" value="<?php echo htmlspecialchars($oneUser['email']); ?>">
                                </td>
                                <td>
                                    <span class="static-span"><?php echo $oneUser['is_admin'] ? 'Yes' : 'No'; ?></span>
                                    <input type="checkbox" class="edit-input" style="display:none;" name="is_admin" <?php echo $oneUser['is_admin'] ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="edit-btn" onclick="toggleEdit(<?php echo $oneUser['id']; ?>)">Edit</button>
                                        <button type="button" class="delete-btn" onclick="deleteUser(<?php echo $oneUser['id']; ?>)">Delete</button>
                                        <div class="edit-form" style="display:none;">
                                            <button type="button" onclick="saveChanges(<?php echo $oneUser['id']; ?>)">Save Changes</button>
                                            <button type="button" onclick="cancelEdit(<?php echo $oneUser['id']; ?>)">Cancel Editing</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <?php include '../Webler/partials/footer.php'; ?>
    <?php include '../Webler/includes/js.php'; ?>

    <script type="text/javascript">
        const userData = <?php echo json_encode($users); ?>;

        function toggleEdit(userId) {
            const staticSpans = document.querySelectorAll(`#row-${userId} .static-span`);
            const editInputs = document.querySelectorAll(`#row-${userId} .edit-input`);
            const actionButtons = document.querySelector(`#row-${userId} .action-buttons`);
            const editForm = actionButtons.querySelector('.edit-form');
            const editButton = actionButtons.querySelector('.edit-btn');

            staticSpans.forEach(span => span.style.display = 'none');
            editInputs.forEach(input => input.style.display = 'inline');

            editForm.style.display = 'block';
            editButton.style.display = 'none';
        }

        function cancelEdit(userId) {
            const staticSpans = document.querySelectorAll(`#row-${userId} .static-span`);
            const editInputs = document.querySelectorAll(`#row-${userId} .edit-input`);
            const actionButtons = document.querySelector(`#row-${userId} .action-buttons`);
            const editForm = actionButtons.querySelector('.edit-form');
            const editButton = actionButtons.querySelector('.edit-btn');

            // Reset inputs to initial values
            const user = userData.find(u => u.id === userId);
            if (user) {
                editInputs.forEach(input => {
                    if (input.name === 'name') {
                        input.value = user.name;
                    } else if (input.name === 'email') {
                        input.value = user.email;
                    } else if (input.name === 'is_admin') {
                        input.checked = user.is_admin == 1;
                    }
                });
            }

            staticSpans.forEach(span => span.style.display = 'inline');
            editInputs.forEach(input => input.style.display = 'none');

            editForm.style.display = 'none';
            editButton.style.display = 'inline';
        }

        function saveChanges(userId) {
        const name = document.querySelector(`#row-${userId} .edit-input[name='name']`).value;
        const email = document.querySelector(`#row-${userId} .edit-input[name='email']`).value;
        const isAdmin = document.querySelector(`#row-${userId} .edit-input[name='is_admin']`).checked ? 1 : 0;

        // Prepare FormData
        const formData = new FormData();
        formData.append('action', 'edit-user');
        formData.append('edit_user_id', userId);
        formData.append('name', name);
        formData.append('email', email);
        formData.append('is_admin', isAdmin);

        fetch('/Webler/api/admin.php', {
            method: 'POST',
            body: formData // Send FormData, not JSON
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh the page to reflect changes
            } else {
                alert('There was an error saving the changes.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            const formData = new FormData();
            formData.append('action', 'delete-user');
            formData.append('delete_user_id', userId);

            fetch('/Webler/api/admin.php', {
                method: 'POST',
                body: formData // Send FormData, not JSON
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Refresh the page to reflect changes
                } else {
                    alert('There was an error deleting the user.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }
    </script>
</body>
</html>