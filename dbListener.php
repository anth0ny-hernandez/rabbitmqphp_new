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
                return true;
            } else {
                // Log and return the error
                error_log("Error in registration: " . $conn->error);
                echo "Error: " . $conn->error . "\n";
                $insert = "Error: " . $conn->error;
                return false;
            }
        case "login":
            $username = $request['username'];
            $password = $request['password'];
        
            echo "Processing login for $username...\n";
            echo "================================\n";
        
            // Query to get the hashed password for the specified username
            $sql = "SELECT password FROM accounts WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $ray = $stmt->get_result();
        
            if ($ray->num_rows > 0) {
                $row = $ray->fetch_assoc();
                
                // Verify the password using password_verify
                if (password_verify($password, $row['password'])) {
                    echo "Login successful for user $username!\n";
                    echo "================================\n";
        
                    // Generate a session token and expiration time (30 seconds from now)
                    $session_token = bin2hex(random_bytes(16)); // Generate a random token
                    $session_expires = time() + 30; // Set the session to expire in 30 seconds
        
                    // Update the database with the session token and expiration time
                    $updateQuery = "UPDATE accounts SET session_token = ?, session_expires = ? WHERE username = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("sis", $session_token, $session_expires, $username);
                    
                    if ($updateStmt->execute()) {
                        // Set the session token cookie with a 30-second expiration
                        
                        
                        // Return a successful response with the session token
                        return array("success" => true, "session_token" => $session_token);
                    } 
                } else {
                    // Password verification failed
                    echo "Incorrect password for user $username!\n";
                    echo "================================\n";
                    return array("success" => false, "message" => "Incorrect password.");
                }
            } else {
                // No user found with the specified username
                echo "User $username not found!\n";
                echo "================================\n";
                return array("success" => false, "message" => "User not found.");
            }
                
        case "checkCache":
            // retrieve parameters from client request
            $label = $request["label"];
            $healthLabels = $request["healthLabels"];
            $calories = $request["ENERC_KCAL"];
            $cuisine = $request["cuisineType"];
            $meal = $request["mealType"];

            $sql = "SELECT * FROM recipes WHERE label = ? 
                    AND healthLabels = ? AND ENERC_KCAL <= ?
                    AND cuisineType = ? AND mealType = ?";
            $stmt = $conn->prepare($sql);
            // KCAL might need to be integer
            $stmt->bind_param("sssss", $label, $healthLabels, $calories, $cuisine, $meal);
            $stmt->execute();
            $arrays = $stmt->get_result();
            if ($arrays->num_rows > 0) {
                $recipes = $ray->fetch_assoc();
                return $recipes;
            } else {
                return false;
            }

        // called when no recipes exist and RMQ server requests & sends API data to insert 
        case "cacheRecipes":
            $queryStatement = "INSERT INTO recipes (label, image, url, healthLabels, 
                                            ENERC_KCAL, ingredientLines, calories, cuisineType, 
                                            mealType, fat, carbs, fiber, sugars, protein, 
                                            cholesterol, sodium, calcium, vitaminA, vitaminC, timestamp)
                                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                                                ?, ?, ?, ?, ?, ? )";
            $query = $conn->prepare($queryStatement);
            $query->bind_param("ssssisissiiiiiiiiiii", 
                                $recipeName, $image, $url, $healthLabels, 
                                $energy, $ingredients, $calories, $cuisineType, 
                                $mealType, $fat, $carbs, $fiber, $sugar, $protein, 
                                $cholesterol, $sodium, $calcium, $vitaminA, 
                                $vitaminC, $time);
            
            if ($query->execute()) {
                echo "Recipe(s) inserted successfully!\n";
                echo "================================\n";
                // $response['query'] = $queryStatement;
                // echo $response['query'];
                return $response;
            } else {
                // Log and return the error
                error_log("Error in registration: " . $conn->error);
                echo "Error: " . $conn->error . "\n";
                $insert = "Error: " . $conn->error;
                return false;
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



// $query = "SELECT session_token FROM users WHERE username=?";
// $statement = $db->prepare($query);
// $statement->bind_param("s", $username);
// if($statement->execute())
// {
//     $result = $statement->get_result();
//     echo "success!";
//     $resultToken=$result->fetch_all();
//     $sessionToken = $resultToken[0][0];
//     echo "client receiveds $sessionToken".PHP_EOL;
//     $expire_time = time() + 10;
//     setcookie('session_token', $sessionToken, $expire_time, "/");
// }

// else
// {
//     echo "fail";
// }
// include('logout.php');

// echo "client receiveds ". $_COOKIE['session_token'].PHP_EOL;
// print_r($response);
// print_r(headers_list());
// print_r($_COOKIE);
// echo $response;
// return $response;

// if(time() > $expire_time)
// {
//     include('logout.php');
// }

?>
