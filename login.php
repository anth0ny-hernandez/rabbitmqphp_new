<?php
session_start(); // Use standard PHP session handling
require_once('rabbitMQLib.inc');

// Database connection
$dbHost = 'sql5.freesqldatabase.com';
$dbName = 'sql5737763';
$dbUser = 'sql5737763';
$dbPassword = 'xSGbpGyEpv';

try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create a client for communicating with the RabbitMQ server
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create a login request
    $request = array();
    $request['type'] = "login";
    $request['username'] = $username;
    $request['password'] = $password;

    // Send the login request via RabbitMQ
    $response = $client->send_request($request);

    // Handle the response from RabbitMQ
    if ($response == "Login successful for user: $username") {
        // Generate a session token and expiration time (30 seconds from now)
        $session_token = bin2hex(random_bytes(16)); // Generate a random token
        $session_expires = time() + 30; // Set the session to expire in 30 seconds

        // Update the database with the session token and expiration time
        $stmt = $db->prepare("UPDATE accounts SET session_token = ?, session_expires = ? WHERE username = ?");
        $stmt->execute([$session_token, $session_expires, $username]);

        // Store session token and user info in the PHP session
        $_SESSION['session_token'] = $session_token;
        $_SESSION['username'] = $username;

        // Redirect to the homepage
        header("Location: index.php");
        exit();
    } else {
        // If login fails, show an error message
        $error_message = "Invalid login credentials. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login Page</h1>

    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="login.php" method="POST">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>

