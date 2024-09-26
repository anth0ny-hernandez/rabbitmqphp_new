<?php
// Database functions for registration, login, and session management

function doRegister($username, $password) {
    // Connect to the old MySQL database
    $db = new mysqli('sql5.freesqldatabase.com', 'sql5733576', 'He5tHy2YhB', 'sql5733576', 3306);

    // Check if the connection was successful
    if ($db->connect_error) {
        return "Connection failed: " . $db->connect_error;
    }

    // Check if username already exists
    $query = $db->prepare("SELECT * FROM users WHERE username=?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        // User already exists
        return "User already exists";
    }

    // If username doesn't exist, hash the password and insert the new user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insertQuery = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $insertQuery->bind_param("ss", $username, $hashedPassword);

    if ($insertQuery->execute()) {
        return "Registration successful";
    } else {
        return "Error: " . $db->error;
    }
}

function doLogin($username, $password) {
    // Connect to the old MySQL database
    $db = new mysqli('sql5.freesqldatabase.com', 'sql5733576', 'He5tHy2YhB', 'sql5733576', 3306);

    // Check if the connection was successful
    if ($db->connect_error) {
        return "Connection failed: " . $db->connect_error;
    }

    // Query database for the username
    $query = $db->prepare("SELECT * FROM users WHERE username=?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows == 0) {
        return "User does not exist";
    }

    $row = $result->fetch_assoc();

    // Verify the password
    if (password_verify($password, $row['password'])) {
        // Generate session token and store it in the database
        $sessionToken = bin2hex(random_bytes(16));
        $updateQuery = $db->prepare("UPDATE users SET session_token=? WHERE username=?");
        $updateQuery->bind_param("ss", $sessionToken, $username);
        $updateQuery->execute();

        return "Login successful, session token: " . $sessionToken;
    } else {
        return "Invalid password";
    }
}

function doLogout($username) {
    // Connect to the old MySQL database
    $db = new mysqli('sql5.freesqldatabase.com', 'sql5733576', 'He5tHy2YhB', 'sql5733576', 3306);

    // Check if the connection was successful
    if ($db->connect_error) {
        return "Connection failed: " . $db->connect_error;
    }

    // Invalidate session token
    $updateQuery = $db->prepare("UPDATE users SET session_token=NULL WHERE username=?");
    $updateQuery->bind_param("s", $username);
    $updateQuery->execute();

    if ($updateQuery->affected_rows > 0) {
        return "Logout successful";
    } else {
        return "Logout failed";
    }
}

function validateSession($sessionToken) {
    // Connect to the old MySQL database
    $db = new mysqli('sql5.freesqldatabase.com', 'sql5733576', 'He5tHy2YhB', 'sql5733576', 3306);

    // Check if the connection was successful
    if ($db->connect_error) {
        return "Connection failed: " . $db->connect_error;
    }

    // Check if the session token exists
    $query = $db->prepare("SELECT * FROM users WHERE session_token=?");
    $query->bind_param("s", $sessionToken);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        return "Session valid";
    } else {
        return "Session invalid";
    }
}
?>
