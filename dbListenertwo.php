<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

// Database connection details (adjust these as needed for your local database)
$host = '127.0.0.1';  // Localhost for the local database
$dbname = 'testdb';  // Replace with your local database name
$username = 'testUser';  // Replace with your database username
$password = '12345';  // Replace with your database password
$port = 3306;

// Establish a connection to the local database
$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Function to process database-related tasks based on request type
function processDatabaseRequest($request)
{
    global $conn;

    // Check if the request type is set
    if (!isset($request['type'])) {
        return array("success" => false, "message" => "ERROR: Unsupported message type");
    }

    // Handle the request based on its type
    switch ($request['type']) {
        case "login":
            $username = $request['username'];
            $password = $request['password'];

            // Query the database to check for user credentials
            $query = "SELECT * FROM users WHERE username = ? AND password = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Login is successful
                // Generate a session token
                $session_token = bin2hex(random_bytes(16));

                // Optionally, store the session token in the database
                $updateQuery = "UPDATE users SET session_token = ? WHERE username = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("ss", $session_token, $username);
                $updateStmt->execute();

                return array("success" => true, "session_token" => $session_token);
            } else {
                // Login failed
                return array("success" => false, "message" => "Invalid username or password");
            }

        case "register":
            $username = $request['username'];
            $password = $request['password'];

            // Insert new user into the database
            $query = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $username, $password);

            if ($stmt->execute()) {
                return array("success" => true, "message" => "Registration successful");
            } else {
                return array("success" => false, "message" => "Registration failed: " . $stmt->error);
            }

        default:
            return array("success" => false, "message" => "ERROR: Unsupported message type");
    }
}

// Create a RabbitMQ client to communicate with the RabbitMQ server
$client = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");

$request = array(
    "type" => "login",  // Or "register" based on what you want to test
    "username" => "testuser",
    "password" => "testpassword"
);


// Send the request to RabbitMQ
$response = $client->send_request($request);

// If the request was processed successfully by RabbitMQ, perform database processing
if ($response['success']) {
    echo "Request was successful. Processing database operation...\n";
    $dbResponse = processDatabaseRequest($response);
    print_r($dbResponse);
} else {
    echo "Request failed: " . $response['message'] . "\n";
}

// Close the database connection
$conn->close();
echo "Database connection closed.\n";
?>
