#!/bin/php
<?php
ob_start();
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

function databaseProcessor($request) {

    echo "Received request: ";
    var_dump($request);

    // database connection & credential variable assignment
    $conn = new mysqli('localhost', 'testUser', '12345', 'testdb');
    $username = $request['username'];
    $password = $request['password'];

    switch($request['type']) {
        case "register":
            echo "Processing username registration...\n";
            echo "================================\n";

            // insert result
            $insert = "";
            // link to source
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO accounts (username, password) VALUES ('$username', '$hashedPassword')";
            if ($conn->query($sql) === TRUE) {
                echo "User $username registered successfully!\n";  // Debugging
                echo "================================\n";
                $insert = "User $username registered successfully!";
            } else {
                // Log and return the error
                error_log("Error in registration: " . $conn->error);
                echo "Error: " . $conn->error . "\n";
                $insert = "Error: " . $conn->error;
            }
            return $insert;
        case "login":
            echo "Processing login for $username...\n";
            echo "================================\n";
            $select = "";

            $sql = "SELECT password FROM accounts WHERE username = '$username'";
            $ray = $conn->query($sql);

            if($ray->num_rows > 0) {
                $row = $ray->fetch_assoc();
                // insert source link
                if(password_verify($password, $row['password'])) {
                    echo "Login successful for user $username !\n";
                    echo "================================\n";
                    $select = "Login successful";
                } else {
                    echo "Incorrect password for user $username !\n";
                    echo "================================\n";
                    $select = "Incorrect password for user $username !";
                }

                // creates cookie
                if ($select == "Login successful") {
                    // Generate a session token and expiration time (30 seconds from now)
                    $session_token = bin2hex(random_bytes(16)); // Generate a random token
                    $session_expires = time() + 30; // Set the session to expire in 30 seconds

                    // Update the database with the session token and expiration time
                    $stmt = $conn->prepare("UPDATE accounts SET session_token = ?, session_expires = ? WHERE username = ?");

                    // Set the session token cookie (expire in 30 seconds)
                    //Source for setting cookie: https://www.w3schools.com/php/func_network_setcookie.asp
                    // Redirect to the homepage
                    if($stmt->execute([$session_token, $session_expires, $username]))
                    {
                        setcookie('session_token', $session_token, $session_expires, "/");
                        return true;
                    }
                } else {
                    // If login fails, show an error message
                    $error_message = "Invalid login credentials. Please try again.";
                    return false;
                }
            }
        default:
            return "Database Client-Server error";
    }
}

// Create a server that listens for requests from clients
$dbServer = new rabbitMQServer("testDB_RMQ.ini", "dbConnect");
ob_end_flush();
echo "RabbitMQ Server is running and waiting for requests...\n";
$dbServer->process_requests('databaseProcessor');
// Close the database connection
$conn->close();

?>
