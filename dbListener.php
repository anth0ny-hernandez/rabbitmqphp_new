#!/bin/php
<?php
ob_start();
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

function checkCache($query) {
    $db = connectDatabase();
    $stmt = $db->prepare("SELECT label, image, url, healthLabels, ENERC_KCAL, ingredientLines, calories, cuisineType, mealtype, fat, Carbs, fiber, sugars, protein, cholesterol, sodium, Calcium, Vitamin_A, Vitamin_C FROM recipes WHERE label = ?");
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $recipes[] = [
            'label' => $row['label'],
            'image' => $row['image'],
            'url' => $row['url'],
            'healthLabels' => $row['healthLabels'],
            'ENERC_KCAL' => $row['ENERC_KCAL'],
            'ingredientLines' => $row['ingredientLines'],
            'calories' => $row['calories'],
            'cuisineType' => $row['cuisineType'],
            'mealtype' => $row['mealtype'],
            'fat' => $row['fat'],
            'Carbs' => $row['Carbs'],
            'fiber' => $row['fiber'],
            'sugars' => $row['sugars'],
            'protein' => $row['protein'],
            'cholesterol' => $row['cholesterol'],
            'sodium' => $row['sodium'],
            'Calcium' => $row['Calcium'],
            'Vitamin_A' => $row['Vitamin_A'],
            'Vitamin_C' => $row['Vitamin_C']
        ];
    }

    $stmt->close();
    $db->close();

    return ['hits' => $recipes];
}

// Cache new recipes in the database
function cacheRecipes($query, $recipes) {
    $db = connectDatabase();
    $stmt = $db->prepare("INSERT INTO recipes (label, image, url, healthLabels, ENERC_KCAL, ingredientLines, calories, cuisineType, mealtype, fat, Carbs, fiber, sugars, protein, cholesterol, sodium, Calcium, Vitamin_A, Vitamin_C, Timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($recipes as $recipe) {
        $label = $recipe['label'];
        $image = $recipe['image'] ?? null;
        $url = $recipe['url'];
        $healthLabels = implode(', ', $recipe['healthLabels'] ?? []);
        $ENERC_KCAL = $recipe['ENERC_KCAL'] ?? null;
        $ingredientLines = implode(', ', $recipe['ingredientLines'] ?? []);
        $calories = $recipe['calories'] ?? null;
        $cuisineType = implode(', ', $recipe['cuisineType'] ?? []);
        $mealtype = implode(', ', $recipe['mealType'] ?? []);
        $fat = $recipe['totalNutrients']['FAT']['quantity'] ?? null;
        $Carbs = $recipe['totalNutrients']['CHOCDF']['quantity'] ?? null;
        $fiber = $recipe['totalNutrients']['FIBTG']['quantity'] ?? null;
        $sugars = $recipe['totalNutrients']['SUGAR']['quantity'] ?? null;
        $protein = $recipe['totalNutrients']['PROCNT']['quantity'] ?? null;
        $cholesterol = $recipe['totalNutrients']['CHOLE']['quantity'] ?? null;
        $sodium = $recipe['totalNutrients']['NA']['quantity'] ?? null;
        $Calcium = $recipe['totalNutrients']['CA']['quantity'] ?? null;
        $Vitamin_A = $recipe['totalNutrients']['VITA_RAE']['quantity'] ?? null;
        $Vitamin_C = $recipe['totalNutrients']['VITC']['quantity'] ?? null;
        $Timestamp = time();

        $stmt->bind_param(
            "ssssdsdssddddddddddd",
            $label, $image, $url, $healthLabels, $ENERC_KCAL, $ingredientLines, $calories, $cuisineType, $mealtype, $fat, $Carbs, $fiber, $sugars, $protein, $cholesterol, $sodium, $Calcium, $Vitamin_A, $Vitamin_C, $Timestamp
        );
        $stmt->execute();
    }

    $stmt->close();
    $db->close();
}

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
