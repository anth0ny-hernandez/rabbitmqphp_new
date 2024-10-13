#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');

// Database connection details
$dbHost = 'sql5.freesqldatabase.com';
$dbName = 'sql5736071';
$dbUser = 'sql5736071';
$dbPassword = 'DCVCqclHF3';

$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

function doRegister($username, $password) {
    global $conn;
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert into the database
    $sql = "INSERT INTO accounts (username, password) VALUES ('$username', '$hashedPassword')";
    if ($conn->query($sql) === TRUE) {
        return "User $username registered successfully";
    } else {
        return "Error: " . $conn->error;
    }
}

function doLogin($username, $password) {
    global $conn;
    
    // Query to check for the user
    $sql = "SELECT password FROM accounts WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            return "Login successful for user: $username";
        } else {
            return "Incorrect password for user: $username";
        }
    } else {
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
            return doLogin($request['username'], $request['password']);
        case "register":
            return doRegister($request['username'], $request['password']);
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

