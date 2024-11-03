#!/bin/php
<?php
ob_start();
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

function databaseProcessor($request) {
    echo "Received request: ";
    var_dump($request);

    // Database connection
    $conn = new mysqli('localhost', 'testUser', '12345', 'testdb');
    if ($conn->connect_error) {
        return ["error" => "Database connection failed: " . $conn->connect_error];
    }

    switch($request['type']) {

        case "register":
            echo "Processing username registration...\n";
            $username = $request['username'];
            $password = password_hash($request['password'], PASSWORD_BCRYPT);
            $sql = "INSERT INTO accounts (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $password);

            if ($stmt->execute()) {
                echo "User $username registered successfully!\n";
                return ["success" => true, "message" => "User registered successfully"];
            } else {
                error_log("Error in registration: " . $conn->error);
                return ["success" => false, "message" => "Error: " . $conn->error];
            }

        case "login":
            $username = $request['username'];
            $password = $request['password'];
            echo "Processing login for $username...\n";
        
            $sql = "SELECT password FROM accounts WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    $session_token = bin2hex(random_bytes(16));
                    $session_expires = time() + 30;

                    $updateQuery = "UPDATE accounts SET session_token = ?, session_expires = ? WHERE username = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("sis", $session_token, $session_expires, $username);
                    
                    if ($updateStmt->execute()) {
                        return ["success" => true, "session_token" => $session_token];
                    } else {
                        return ["success" => false, "message" => "Failed to set session token"];
                    }
                } else {
                    return ["success" => false, "message" => "Incorrect password."];
                }
            } else {
                return ["success" => false, "message" => "User not found."];
            }

        case "searchRecipe":
            $label = $request["label"];
            $healthLabels = $request["healthLabels"] ?? "";
            $calories = $request["ENERC_KCAL"] ?? 0;
            $cuisineType = $request["cuisineType"] ?? "";
            $mealType = $request["mealType"] ?? "";

            $sql = "SELECT * FROM recipes WHERE label = ? AND healthLabels = ? AND ENERC_KCAL <= ? AND cuisineType = ? AND mealType = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiss", $label, $healthLabels, $calories, $cuisineType, $mealType);
            $stmt->execute();
            $result = $stmt->get_result();

            $recipes = [];
            while ($row = $result->fetch_assoc()) {
                $recipes[] = $row;
            }

            if (!empty($recipes)) {
                return ["hits" => $recipes];
            } else {
                return ["success" => false, "message" => "No recipes found."];
            }

        case "insertRecipe":
            $recipes = $request['recipes'];
            $queryStatement = "INSERT INTO recipes (label, image, url, healthLabels, ENERC_KCAL, ingredientLines, calories, cuisineType, mealType, fat, Carbs, fiber, sugars, protein, cholesterol, sodium, Calcium, Vitamin_A, Vitamin_C, Timestamp) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($queryStatement);

            foreach ($recipes as $recipe) {
                $label = $recipe['label'];
                $image = $recipe['image'] ?? null;
                $url = $recipe['url'];
                $healthLabels = implode(', ', $recipe['healthLabels'] ?? []);
                $ENERC_KCAL = $recipe['ENERC_KCAL'] ?? 0;
                $ingredientLines = implode(', ', $recipe['ingredientLines'] ?? []);
                $calories = $recipe['calories'] ?? 0;
                $cuisineType = implode(', ', $recipe['cuisineType'] ?? []);
                $mealType = implode(', ', $recipe['mealType'] ?? []);
                $fat = $recipe['totalNutrients']['FAT']['quantity'] ?? 0;
                $Carbs = $recipe['totalNutrients']['CHOCDF']['quantity'] ?? 0;
                $fiber = $recipe['totalNutrients']['FIBTG']['quantity'] ?? 0;
                $sugars = $recipe['totalNutrients']['SUGAR']['quantity'] ?? 0;
                $protein = $recipe['totalNutrients']['PROCNT']['quantity'] ?? 0;
                $cholesterol = $recipe['totalNutrients']['CHOLE']['quantity'] ?? 0;
                $sodium = $recipe['totalNutrients']['NA']['quantity'] ?? 0;
                $Calcium = $recipe['totalNutrients']['CA']['quantity'] ?? 0;
                $Vitamin_A = $recipe['totalNutrients']['VITA_RAE']['quantity'] ?? 0;
                $Vitamin_C = $recipe['totalNutrients']['VITC']['quantity'] ?? 0;
                $timestamp = time();

                $stmt->bind_param("ssssisissddddddddddd", 
                    $label, $image, $url, $healthLabels, $ENERC_KCAL, $ingredientLines, 
                    $calories, $cuisineType, $mealType, $fat, $Carbs, $fiber, $sugars, 
                    $protein, $cholesterol, $sodium, $Calcium, $Vitamin_A, $Vitamin_C, $timestamp);

                if (!$stmt->execute()) {
                    error_log("Error inserting recipe: " . $stmt->error);
                }
            }

            return ["success" => true, "message" => "Recipes inserted successfully."];

        default:
            return ["error" => "Unsupported request type"];
    }
}

// Create a server that listens for requests from clients
$dbServer = new rabbitMQServer("testDB_RMQ.ini", "dbConnect");
ob_end_flush();
echo "RabbitMQ Server is running and waiting for requests...\n";
$dbServer->process_requests('databaseProcessor');
$conn->close();
?>
