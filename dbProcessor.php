<?php
// Database functions for registration, login, and session management

function doRegister($username, $password) {
    // Connect to the MySQL database using the provided credentials
    $db = new mysqli('sql5.freesqldatabase.com', 'sql5736071', 'DCVCqclHF3', 'sql5736071', 3306);

    // Check if the connection to the database was successful
    if ($db->connect_error) {
        return array("status" => "fail", "message" => "Connection failed: " . $db->connect_error);
    }

    // Check if username already exists
    $query = $db->prepare("SELECT * FROM users WHERE username=?");
    $query->bind_param("s", $username);  
    $query->execute();  
    $result = $query->get_result();  

    if ($result->num_rows > 0) {
        return array("status" => "fail", "message" => "User already exists");
    }

    // Hash the user's password for secure storage in the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare an SQL query to insert the new user into the 'users' table
    $insertQuery = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $insertQuery->bind_param("ss", $username, $hashedPassword);

    // Execute the query and return a success message along with a generated session token
    if ($insertQuery->execute()) {
        // Generate a session token for the user
        $sessionToken = bin2hex(random_bytes(16));
        $expiration = time() + 120; // Set the session expiration time to 2 minutes

        // Store the session token and expiration time in the database
        $updateQuery = $db->prepare("UPDATE users SET session_token=?, session_expires=? WHERE username=?");
        $updateQuery->bind_param("sis", $sessionToken, $expiration, $username);
        $updateQuery->execute();

        return array("status" => "success", "message" => "Registration successful", "session_token" => $sessionToken);
    } else {
        return array("status" => "fail", "message" => "Error: " . $db->error);
    }
}

function doLogin($username, $password) {
    // Connect to the MySQL database
    $db = new mysqli('sql5.freesqldatabase.com', 'sql5736071', 'DCVCqclHF3', 'sql5736071', 3306);

    // Check if the connection was successful
    if ($db->connect_error) {
        return array("status" => "fail", "message" => "Connection failed: " . $db->connect_error);
    }

    // Query to fetch user by username
    $query = $db->prepare("SELECT * FROM users WHERE username=?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    // If user exists, verify the password
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // If password matches, generate a session token
            $sessionToken = bin2hex(random_bytes(16));
            $updateQuery = $db->prepare("UPDATE users SET session_token=? WHERE username=?");
            $updateQuery->bind_param("ss", $sessionToken, $username);
            $updateQuery->execute();

            // Return success with the session token
            return array("status" => "success", "session_token" => $sessionToken);
        } else {
            return array("status" => "fail", "message" => "Invalid password");
        }
    } else {
        return array("status" => "fail", "message" => "User not found");
    }
}


function validateSession($sessionToken) {
   // Connect to the MySQL database
   $db = new mysqli('sql5.freesqldatabase.com', 'sql5736071', 'DCVCqclHF3', 'sql5736071', 3306);

    // Check if the connection was successful
    if ($db->connect_error) {
        return "Connection failed: " . $db->connect_error;
    }

    // Prepare a query to validate the session token and check if it's still valid
    $query = $db->prepare("SELECT session_expires FROM users WHERE session_token=?");
    $query->bind_param("s", $sessionToken);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentTime = time();

        if ($currentTime > $row['session_expires']) {
            return "Session expired";  // The session has expired
        } else {
            return "Session valid";  // The session is still valid
        }
    } else {
        return "Invalid session";
    }
}
?>

