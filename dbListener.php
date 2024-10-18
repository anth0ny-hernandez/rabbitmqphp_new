#!/usr/bin/php
<?php
require_once('rabbitMQLib.inc');
require_once('path.inc');
require_once('get_host_info.inc');

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
            $insert = null;
            // link to source
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO accounts (username, password) VALUES ('$username', '$hashedPassword')";
            if ($conn->query($sql) === true) {
                echo "User $username registered successfully!\n";  // Debugging
                echo "================================\n";
                $insert = true;
            } else {
                // Log and return the error
                error_log("Error in registration: " . $conn->error);
                echo "Error: " . $conn->error . "\n";
                $insert = false;
            }
            // Close the database connection
            $conn->close();
            return $insert;
        case "login":
            echo "Processing login for $username...\n";
            echo "================================\n";
            $select = null;

            $sql = "SELECT password FROM accounts WHERE username = '$username'";
            $ray = $conn->query($sql);

            if($ray->num_rows > 0) {
                $row = $ray->fetch_assoc();
                // insert source link
                if(password_verify($password, $row['password'])) {
                    echo "Login successful for user $username !\n";
                    echo "================================\n";
                    $select = true;
                } else {
                    echo "Incorrect password for user $username !\n";
                    echo "================================\n";
                    $select = false;
                }

                
            }
            // Close the database connection
            $conn->close();
            return $select;
        default:
            return "Database Client-Server error";
    }
}

// Create a server that listens for requests from clients
$dbServer = new rabbitMQServer("testDB_RMQ.ini", "dbConnect");

echo "RabbitMQ Server is running and waiting for requests...\n";
$dbServer->process_requests('databaseProcessor');

?>
