<?php
// Database functions for registration, login, and session management

function doRegister($username, $password) {
    // Connect to  MySQL database. Using free server. 
    $db = new mysqli('127.0.0.1', "testUser", '12345', 'testdb');

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

    // If username doesn't exist, hash the password for security and insert the new user
    //Password hashing resource: 
    //https://stackoverflow.com/questions/30279321/how-to-use-phps-password-hash-to-hash-and-verify-passwords
    
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
    // Connect to the free MySQL database
    $db = new mysqli('127.0.0.1', "testUser", '12345', 'testdb');

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
        // Source for alphanumeric string generation: https://stackoverflow.com/questions/1846202/how-to-generate-a-random-unique-alphanumeric-string
        $bytes = random_bytes(20);
        $sessionToken = (bin2hex($bytes));

        $updateQuery = $db->prepare("UPDATE users SET session_token=? WHERE username=?");
        $updateQuery->bind_param("ss", $sessionToken, $username);
        $updateQuery->execute();

        return "Login successful, session token: " . $sessionToken;
    } else {
        return "Invalid password";
    }

   // if(sleep()
}

function doLogout($username) {
    // Connect to the MySQL database
    $db = new mysqli('127.0.0.1', "testUser", '12345', 'testdb');

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
    // Connect to the MySQL database
    $db = new mysqli('127.0.0.1', "testUser", '12345', 'testdb');

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

