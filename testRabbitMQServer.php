#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');

// Database connection details
$dbHost = 'sql5.freesqldatabase.com';
$dbName = 'sql5737763';
$dbUser = 'sql5737763';
$dbPassword = 'xSGbpGyEpv';

$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

function doRegister($username, $password) {
    global $conn;
    
    // Debugging: Log that registration is being processed
    echo "Processing registration for $username...\n";
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert into the database
    $sql = "INSERT INTO accounts (username, password) VALUES ('$username', '$hashedPassword')";
    if ($conn->query($sql) === TRUE) {
        echo "User $username registered successfully.\n";  // Debugging
        return "User $username registered successfully";
    } else {
        // Log and return the error
        error_log("Error in registration: " . $conn->error);
        echo "Error: " . $conn->error . "\n";
        return "Error: " . $conn->error;
    }
}

function doLogin($username, $password) {
    global $conn;
    
    // Debugging: Log that login is being processed
    echo "Processing login for $username...\n";
    
    // Query to check for the user
    $sql = "SELECT password FROM accounts WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            echo "Login successful for user: $username\n";  // Debugging
            return "Login successful for user: $username";
        } else {
            echo "Incorrect password for user: $username\n";  // Debugging
            return "Incorrect password for user: $username";
        }
    } else {
        echo "User $username not found\n";  // Debugging
        return "User $username not found";
    }
}

function requestProcessor($request) {
    echo "Received request: ";
    var_dump($request);

    if (!isset($request['type'])) {
        return "ERROR: unsupported message type";
    }

    switch ($request['type']) {
        case "login":
            $result = doLogin($request['username'], $request['password']);
            echo "Sending response for login: $result\n";  // Debugging output
            return $result;
        case "register":
            $result = doRegister($request['username'], $request['password']);
            echo "Sending response for registration: $result\n";  // Debugging output
            return $result;
        default:
            return "ERROR: unsupported message type";
    }
}

// Create a server that listens for requests from clients
$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");

echo "RabbitMQ Server is running and waiting for requests...\n";
$server->process_requests('requestProcessor');

// Close the database connection
$conn->close();
?>

