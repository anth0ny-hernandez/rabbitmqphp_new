<?php
require_once('rabbitMQLib.inc');

// Check if the session token cookie is set
if (!isset($_COOKIE['session_token'])) {
    // If not set, redirect to login page
    header("Location: login.php");
    exit();
}

// Get the session token from the cookie
$session_token = $_COOKIE['session_token'];

// Refresh the cookie to extend the expiration by another 30 seconds
$expire_time = time() + 30;
setcookie('session_token', $session_token, $expire_time, "/");

// Function to send the search query to RabbitMQ
function sendSearchQuery($query) {
    $client = new rabbitMQClient("testRabbit.MQServer.ini", "testClient"); 
    $request = array();
    $request['type'] = "search";
    $request['query'] = $query;

    $response = $client->send_request($request); // Send the request to RabbitMQ
    return $response; // Return the response from RabbitMQ
}

// Initialize an empty variable for results
$results = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchQuery = $_POST['search'];
    
    // Send the search query to RabbitMQ and get the response
    $response = sendSearchQuery($searchQuery);
    
    // Check if the response contains results
    if ($response) {
        // Assuming the response is an array of results
        $results = '<h2>Search Results for: ' . htmlspecialchars($searchQuery) . '</h2>';
        foreach ($response as $item) {
            $results .= '<div class="result-item">';
            $results .= '<h3>' . htmlspecialchars($item['name']) . '</h3>';
            $results .= '<p>' . htmlspecialchars($item['description']) . '</p>';
            $results .= '</div>';
        }
    } else {
        $results = '<p>No results found.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Bar</title>
    <style>
    header {
            background-color: #333;
            color: #fff;
            padding: 1rem;
            text-align: center;
        }

        body {
            font-family: Arial, sans-serif;
            margin-top: 50px;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: grey;
            text-align: center;
        }

        h1, h2 {
            font-size: 48px;
            font-weight: bold;
            color: lightgreen;
            text-align: center;
        }

        p {
            font-size: 20px;
            font-weight: bold;
            color: lightgreen;
        }

        .button {
            background-color: #ffffff;
            color: black;
            border: .5px solid #333;
            border-radius: 8px;
            transition: background-color 0.3s;
            padding: 10px 20px;
            font-size: 18px;
            margin: 10px;
        }

        button:hover {
            background-color: #555;
        }

        form {
            font-size: 20px;
            font-weight: bold;
            color: lightgreen;
        }


        input[type="text"] {
            font-size: 24px;
            padding: 15px; 
            width: 300px; 
            border-radius: 5px;
            margin-bottom: 10px;
        }

        input[type="submit"] {
            background-color: #ffffff;
            color: black;
            border: .5px solid #333;
            border-radius: 8px;
            transition: background-color 0.3s;
            padding: 10px 20px;
            font-size: 18px;
            margin: 10px;
        } 

        footer {
            margin-top: 100px;
        }
    </style>
</head>
    <body>

    <form method="POST" action="">
        <input type="text" name="search" placeholder="Search items..." required>
        <input type="submit" value="Search">
    </form>

    <div class="results">
        <?php echo $results; ?>
    </div>

    <!-- JavaScript to handle automatic logout after session expiration -->
    <script>
        setTimeout(function() {
            // Delete the session token cookie by setting its expiration date in the past
            document.cookie = 'session_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            // Redirect to the login page
            window.location.href = 'login.php';
        }, 30000); // 30,000 milliseconds = 30 seconds
    </script>

    </body>
    <footer>
        <a href="index.php"><button class="button">Home</button></a>
        <a href="login.php"><button class="button">Login</button></a>
        <a href="registration.php"><button class="button">Register</button></a>
        <a href="dietrestrictions.php"><button class="button">Diet Restricitons</button></a>
    </footer>
</html>
