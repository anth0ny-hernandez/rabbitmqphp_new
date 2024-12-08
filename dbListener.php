#!/bin/php
<?php
ob_start();
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');
require_once('LogProd.php');

// Test an error and log it
// $errorMessage = "Error occurred in dbListener!";
// logErrorAndSend($errorMessage);

function databaseProcessor($request) {

    echo "Received request: ";
    var_dump($request);

    // database connection & credential variable assignment
    $conn = new mysqli('localhost', 'testUser', '12345', 'testdb');
    $username = $request['username'];
    $password = $request['password'];

    switch($request['type']) {
        case "submitReview":
            $username = $request['username'];
            $rating = $request['rating'];
            $feedback = $request['feedback'];
        
            $stmt = $conn->prepare("INSERT INTO reviews (username, rating, feedback) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $username, $rating, $feedback);
        
            if ($stmt->execute()) {
                return ["success" => true];
            } else {
                logErrorAndSend("Error in submitReview: " . $conn->error);
                return ["success" => false, "message" => $conn->error];
            }
        
        case "fetchReviews":
            $query = "SELECT username, rating, feedback, created_at FROM reviews ORDER BY created_at DESC";
            $result = $conn->query($query);
            if (!$result) {
                logErrorAndSend("Error fetching reviews: " . $conn->error);
                return ["success" => false, "message" => $conn->error];
            }

            $reviews = [];
            while ($row = $result->fetch_assoc()) {
                $reviews[] = $row;
            }
        
            return ["success" => true, "reviews" => $reviews];
        
        case "getUserPreferences":
            $session_token = $request['session_token'];
        
            $userQuery = "SELECT id FROM accounts WHERE session_token = ?";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->bind_param("s", $session_token);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
        
            if ($userResult->num_rows > 0) {
                $user = $userResult->fetch_assoc();
                $user_id = $user['id'];
        
                $prefQuery = "SELECT dietaryRestrictions, allergyType, otherRestrictions FROM preferences WHERE id = ?";
                $prefStmt = $conn->prepare($prefQuery);
                $prefStmt->bind_param("i", $user_id);
                $prefStmt->execute();
                $prefResult = $prefStmt->get_result();
        
                if ($prefResult->num_rows > 0) {
                    $preferences = $prefResult->fetch_assoc();
                    return array_merge(["success" => true], $preferences);
                } else {
                    logErrorAndSend("No dietary preferences found for user ID $user_id.");
                    return ["success" => false, "message" => "No dietary preferences found."];
                }
            } else {
                logErrorAndSend("User not found for session token $session_token.");
                return ["success" => false, "message" => "User not found."];
            }

        // Repeat similar changes for other cases (error logging added)

        default:
            logErrorAndSend("Unhandled request type: " . $request['type']);
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
