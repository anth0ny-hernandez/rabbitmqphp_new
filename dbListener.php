#!/bin/php
<?php
ob_start();
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

function databaseProcessor($request) {
$data2 =json_decode($request, true);
    echo "Received request: ";
    var_dump($data2);

    // database connection & credential variable assignment
    $conn = new mysqli('localhost', 'testUser', '12345', 'testdb');
    // $username = $data2['username'];
    // $password = $data2['password'];
    
    switch($data2['hits']['recipe']['type']) {

        case "getUserPreferences":
            $session_token = $data2['session_token'];
        
            // Retrieve user ID based on session token
            $userQuery = "SELECT id FROM accounts WHERE session_token = ?";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->bind_param("s", $session_token);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
        
            if ($userResult->num_rows > 0) {
                $user = $userResult->fetch_assoc();
                $user_id = $user['id'];
        
                // Retrieve dietary preferences
                $prefQuery = "SELECT dietaryRestrictions, allergyType, otherRestrictions FROM preferences WHERE id = ?";
                $prefStmt = $conn->prepare($prefQuery);
                $prefStmt->bind_param("i", $user_id);
                $prefStmt->execute();
                $prefResult = $prefStmt->get_result();
        
                if ($prefResult->num_rows > 0) {
                    $preferences = $prefResult->fetch_assoc();
                    return array_merge(["success" => true], $preferences);
                } else {
                    return ["success" => false, "message" => "No dietary preferences found."];
                }
            } else {
                return ["success" => false, "message" => "User not found."];
            }
        

        case "getDietRestrictions":
            $session_token = $data2['session_token'];
        
            // Find the user ID using the session token
            $userQuery = "SELECT id FROM accounts WHERE session_token = ?";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->bind_param("s", $session_token);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
        
            if ($userResult->num_rows > 0) {
                $user = $userResult->fetch_assoc();
                $user_id = $user['id'];
        
                // Retrieve dietary restrictions
                $prefQuery = "SELECT dietaryRestrictions, allergyType, otherRestrictions FROM preferences WHERE id = ?";
                $prefStmt = $conn->prepare($prefQuery);
                $prefStmt->bind_param("i", $user_id);
                $prefStmt->execute();
                $prefResult = $prefStmt->get_result();
        
                if ($prefResult->num_rows > 0) {
                    $preferences = $prefResult->fetch_assoc();
                    return array_merge(["success" => true], $preferences);
                } else {
                    return ["success" => false, "message" => "No dietary restrictions found."];
                }
            } else {
                return ["success" => false, "message" => "User not found."];
            }
        

        case "dietRestrictions":
            echo "Processing dietary restrictions...\n";

            // Retrieve dietary restriction details
            $dietaryRestrictions = is_array($data2['dietaryRestrictions']) ? implode(", ", $data2['dietaryRestrictions']) : $data2['dietaryRestrictions'];
            $allergyType = is_array($data2['allergyType']) ? implode(", ", $data2['allergyType']) : $data2['allergyType'];
            $otherRestrictions = $data2['otherRestrictions'];
            $session_token = $data2['session_token'];

            // Find the user ID associated with the session token
            $userQuery = "SELECT id FROM accounts WHERE session_token = ?";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->bind_param("s", $session_token);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            
            if ($userResult->num_rows > 0) {
                $user = $userResult->fetch_assoc();
                $user_id = $user['id'];

                // Insert or update dietary preferences in the preferences table
                $prefQuery = "INSERT INTO preferences (id, dietaryRestrictions, allergyType, otherRestrictions) 
                            VALUES (?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE 
                            dietaryRestrictions = VALUES(dietaryRestrictions),
                            allergyType = VALUES(allergyType),
                            otherRestrictions = VALUES(otherRestrictions)";
                
                $prefStmt = $conn->prepare($prefQuery);
                $prefStmt->bind_param("isss", $user_id, $dietaryRestrictions, $allergyType, $otherRestrictions);

                if ($prefStmt->execute()) {
                    echo "Dietary restrictions saved successfully.\n";
                    return array("success" => true, "message" => "Dietary restrictions saved successfully.");
                } else {
                    error_log("Error saving dietary restrictions: " . $conn->error);
                    return array("success" => false, "message" => "Failed to save dietary restrictions.");
                }
            } else {
                echo "User not found for the session token provided.\n";
                return array("success" => false, "message" => "User not found.");
            }

        

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
            $username = $data2['username'];
            $password = $data2['password'];
        
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
            case "searchRecipe":
                // retrieve parameters from client request
                $label = $data2["label"];
                $healthLabels = $data2["healthLabels"];
                $calories = $data2["ENERC_KCAL"];
                $cuisine = $data2["cuisineType"];
                $meal = $data2["mealType"];
    
                $sql = "SELECT * FROM recipes WHERE label = ? 
                        AND healthLabels = ? AND ENERC_KCAL <= ?
                        AND cuisineType = ? AND mealType = ?";
                $stmt = $conn->prepare($sql);
                // KCAL might need to be integer
                $stmt->bind_param("ssiss", $label, $healthLabels, $calories, $cuisine, $meal);
                $stmt->execute();
                $arrays = $stmt->get_result();
                if ($arrays->num_rows > 0) {
                    $recipes = $arrays->fetch_assoc();
                    return $recipes;
                } else {
                    return false;
                }
    
            // called when no recipes exist and RMQ server requests & sends API data to insert 
            case "insertRecipe":

                foreach($data2['hits'] as $hit){
                $time = time();
                $recipe = $hit['recipe'];
                $queryStatement = "INSERT INTO recipes (label, image, url, healthLabels, 
                                ENERC_KCAL, ingredientLines, calories, cuisineType, 
                                mealType, fat, carbs, fiber, sugars, protein, 
                                cholesterol, sodium, calcium, vitaminA, vitaminC, timestamp)
                                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                                        ?, ?, ?, ?, ?, ? )";
                $query = $conn->prepare($queryStatement);

                $recipeName = $recipe['label'];
                $image = $recipe['image'];
                $url = $recipe['url'];
                $healthLabels = $recipe['healthLabels'];
                $energy = $recipe['ENERC_KCAL'];
                $ingredients = $recipe['ingredientLines'];
                $calories = $recipe['calories'];
                $cuisineType = $recipe['cuisineType'];
                $mealType = $recipe['mealType'];
                $fat = $recipe['FAT'];
                $carbs = $recipe['carbs'];
                $fiber = $recipe['fiber'];
                $sugar = $recipe['sugar'];
                $protein = $recipe['protein'];
                $cholesterol = $recipe['cholesterol'];
                $sodium = $recipe['sodium'];
                $calcium = $recipe['calcium'];
                $vitaminA = $recipe['vitaminA'];
                $vitaminC = $recipe['vitaminC'];
                $time = $recipe['time'];

                $query->bind_param("ssssisissiiiiiiiiiii", 
                                    $recipeName, $image, $url, $healthLabels, 
                                    $energy, $ingredients, $calories, $cuisineType, 
                                    $mealType, $fat, $carbs, $fiber, $sugar, $protein, 
                                    $cholesterol, $sodium, $calcium, $vitaminA, 
                                    $vitaminC, $time
                                );
    
                if ($query->execute()) {
                    echo "Recipe(s) inserted successfully!\n";
                    echo "================================\n";
                    // $recipesArray = selectRecipes($data2, $conn); // uses function akin to searchRecipe case
                    // // $response['query'] = $queryStatement;
                    // // echo $response['query'];
                    // return $recipesArray;
                } else {
                    //Log and return the error
                    error_log("Error in registration: " . $conn->error);
                    echo "Error: " . $conn->error . "\n";
                    // $insert = "Error: " . $conn->error;
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
