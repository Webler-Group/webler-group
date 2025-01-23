<?php

// Include the configuration file
require_once __DIR__ . '/../../config.php';

try {
    // Create a PDO instance (connect to the database)
    $pdo = new PDO($CFG->dbDSN, $CFG->dbUser, $CFG->dbPassword, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // SQL statement to create the users table if it does not exist
    $sqlUsers = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_admin TINYINT(1) NOT NULL DEFAULT 0,
        bio TEXT
    )";

    // Execute the query for users table creation
    $pdo->exec($sqlUsers);
    echo "Table 'users' created or already exists.\n";

    // Check and add new columns to the users table if they do not exist
    $addColumnSql = function($tableName, $columnName, $columnDefinition) use ($pdo) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                              WHERE TABLE_NAME = :tableName AND COLUMN_NAME = :columnName");
        $stmt->execute([':tableName' => $tableName, ':columnName' => $columnName]);
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("ALTER TABLE $tableName ADD $columnDefinition");
            echo "Column '$columnName' added to '$tableName'.\n";
        } else {
            echo "Column '$columnName' already exists in '$tableName'.\n";
        }
    };

    $addColumnSql('users', 'name', "name VARCHAR(255)");
    $addColumnSql('users', 'is_active', "is_active TINYINT(1) NOT NULL DEFAULT 1");

    // SQL statement to create the tokens table if it does not exist
    $sqlTokens = "CREATE TABLE IF NOT EXISTS tokens (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        token_type INT NOT NULL,
        expire_date DATETIME NOT NULL,
        user_id INT(11) NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";

    // Execute the query for tokens table creation
    $pdo->exec($sqlTokens);
    echo "Table 'tokens' created or already exists.\n";

    $addColumnSql('tokens', 'value', "value VARCHAR(255)");

    // Check if an admin user already exists
    $query = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $query->execute([':email' => $CFG->adminEmail]);
    $userExists = $query->fetchColumn();

    if (!$userExists) {
        // Prepare to insert an admin user
        $hashedPassword = password_hash($CFG->adminPassword, PASSWORD_DEFAULT);

        $insert = $pdo->prepare("INSERT INTO users (email, password, is_admin) VALUES (:email, :password, :is_admin)");
        $insert->execute([
            ':email' => $CFG->adminEmail,
            ':password' => $hashedPassword,
            ':is_admin' => 1
        ]);

        echo "Admin account created.\n";
    } else {
        echo "Admin account already exists.\n";
    }

    // Update the admin user's name to WeblerCodes
    $updateName = $pdo->prepare("UPDATE users SET name = :name WHERE email = :email");
    $updateName->execute([
        ':name' => 'WeblerCodes',
        ':email' => $CFG->adminEmail
    ]);

    echo "Admin name set to WeblerCodes.\n";

} catch (\PDOException $e) {
    // Handle connection errors
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

?>