<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

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
    </body>
    <footer>
        <a href="login.php"><button class="button">Login</button></a>
        <a href="registration.php"><button class="button">Register</button></a>
        <a href="dietrestrictions.php"><button class="button">Diet Restricitons</button></a>
    </footer>
</html>
