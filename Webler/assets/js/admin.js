window.admin = (function () {
    let userData = [];

    loadUsers();

    const createUserBtn = document.getElementById('createUserBtn');

    document.getElementById('createUserBtn').addEventListener('click', function () {
        const newRow = document.getElementById('row-new');
        newRow.style.display = ''; // Show the new user row
        this.disabled = true; // Disable the "Create User" button to prevent multiple rows
    });

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

    function loadUsers() {
        // Create a request to fetch users from the server
        fetch('/Webler/api/admin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ action: 'get-users' })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    userData = data.data; // Assuming server sends back a JSON object with `users` array
                    const userTableBody = document.getElementById('userTableBody');

                    // Clear existing table rows except for the 'row-new'
                    Array.from(userTableBody.children).forEach(row => {
                        if (row.id !== 'row-new') {
                            row.remove();
                        }
                    });

                    // Populate the table with the new users
                    userData.forEach(user => {
                        createUserRow(user);
                    });
                } else {
                    alert('There was an error loading the users.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
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
                    updateUser(data.data); // Use the extracted function to update userData and DOM
                } else {
                    alert('There was an error saving the changes.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                // Hide edit inputs and show updated static spans
                const editInputs = document.querySelectorAll(`#row-${userId} .edit-input`);
                editInputs.forEach(input => input.style.display = 'none');

                const staticSpans = document.querySelectorAll(`#row-${userId} .static-span`);
                staticSpans.forEach(span => span.style.display = 'inline');

                // Reset action buttons
                const actionButtons = document.querySelector(`#row-${userId} .action-buttons`);
                const editForm = actionButtons.querySelector('.edit-form');
                const editButton = actionButtons.querySelector('.edit-btn');

                editForm.style.display = 'none';
                editButton.style.display = 'inline';
            });
    }

    function updateUser(updatedUser) {
        // Update userData array
        const userIndex = userData.findIndex(user => user.id === updatedUser.id);
        if (userIndex > -1) {
            userData[userIndex] = updatedUser;
        }

        // Update DOM to reflect the changes
        const staticSpans = document.querySelectorAll(`#row-${updatedUser.id} .static-span`);
        staticSpans.forEach(span => {
            const name = span.dataset.name;
            if (name in updatedUser) {
                span.textContent = name == "is_admin" ? updatedUser[name] == 1 ? "Yes" : "No" : updatedUser[name];
            }
        });
    }

    function saveNewUser() {
        const name = document.querySelector(`#row-new .edit-input[name='name']`).value;
        const email = document.querySelector(`#row-new .edit-input[name='email']`).value;
        const isAdmin = document.querySelector(`#row-new .edit-input[name='is_admin']`).checked ? 1 : 0;

        if (!email) {
            alert('Email is required!');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'create-user');
        formData.append('name', name);
        formData.append('email', email);
        formData.append('is_admin', isAdmin);

        fetch('/Webler/api/admin.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const newUser = data.data;

                    // Update the userData array
                    userData.push(newUser);

                    createUserRow(newUser);

                    // Hide the temporary new row
                    cancelNewUser();
                } else {
                    alert('There was an error creating the user.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function cancelNewUser() {
        const newRow = document.getElementById('row-new');
        if (newRow) {
            newRow.style.display = 'none'; // Hide the new user row
            createUserBtn.disabled = false; // Re-enable the "Create User" button
        }
    }

    function deleteUser(userId) {
        // Ask for confirmation
        if (confirm('Are you sure you want to delete this user?')) {
            // Prepare FormData
            const formData = new FormData();
            formData.append('action', 'delete-user');
            formData.append('delete_user_id', userId);

            // Send the POST request to delete the user
            fetch('/Webler/api/admin.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the user row from the DOM
                        const userRow = document.getElementById(`row-${userId}`);
                        if (userRow) {
                            userRow.remove();
                        }

                        // Optionally, remove the user from the userData array if needed
                        const userIndex = userData.findIndex(user => user.id === userId);
                        if (userIndex > -1) {
                            userData.splice(userIndex, 1);
                        }

                        alert('User successfully deleted.');
                    } else {
                        alert('There was an error deleting the user.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    }

    function htmlEscape(str) {
        return String(str).replace(/[&<>"'`=\/]/g, function (s) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '=': '&#x3D;',
                '`': '&#x60;'
            }[s];
        });
    }

    function createUserRow(user) {
        const userRow = document.createElement('tr');
        userRow.id = `row-${user.id}`;
        userRow.innerHTML = `
                        <td>${htmlEscape(user.id)}</td>
                        <td>
                            <span data-name="name" class="static-span admin-static-span">${htmlEscape(user.name || '')}</span>
                            <input autocomplete="off" type="text" class="edit-input admin-edit-input" style="display:none;" name="name" value="${htmlEscape(user.name || '')}">
                        </td>
                        <td>
                            <span data-name="email" class="static-span admin-static-span">${htmlEscape(user.email)}</span>
                            <input autocomplete="off" type="email" class="edit-input admin-edit-input" style="display:none;" name="email" value="${htmlEscape(user.email)}">
                        </td>
                        <td>
                            <span data-name="is_admin" class="static-span admin-static-span">${user.is_admin ? 'Yes' : 'No'}</span>
                            <input type="checkbox" class="edit-input admin-edit-input" style="display:none;" name="is_admin" ${user.is_admin ? 'checked' : ''}>
                        </td>
                        <td>
                            <div class="action-buttons admin-action-buttons">
                                <button type="button" class="edit-btn admin-edit-btn" onclick="admin.toggleEdit(${user.id})">Edit</button>
                                <button type="button" class="delete-btn admin-delete-btn" onclick="admin.deleteUser(${user.id})">Delete</button>
                                <div class="edit-form admin-edit-form" style="display:none;">
                                    <button type="button" class="save-btn admin-save-btn" onclick="admin.saveChanges(${user.id})">Save Changes</button>
                                    <button type="button" class="cancel-btn admin-cancel-btn" onclick="admin.cancelEdit(${user.id})">Cancel Editing</button>
                                </div>
                            </div>
                        </td>
                    `;
        const userTableBody = document.getElementById('userTableBody');
        const newUserRow = document.getElementById('row-new');
        userTableBody.insertBefore(userRow, newUserRow); // Insert the new user row before 'row-new'
    }

    return {
        saveNewUser,
        cancelNewUser,
        toggleEdit,
        deleteUser,
        saveChanges,
        cancelEdit
    }

})();