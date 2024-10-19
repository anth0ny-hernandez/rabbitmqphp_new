<?php
require_once('rabbitMQLib.inc'); // RabbitMQ library
require_once('path.inc');
require_once('get_host_info.inc');

// Database connection (local)
$dbHost = 'localhost';  // Assuming this listener runs on the database host
$dbName = 'your_database_name';
$dbUser = 'your_database_user';
$dbPassword = 'your_database_password';

try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to handle the requests coming from RabbitMQ
function requestProcessor($request) {
    global $db;

    if (!isset($request['type'])) {
        return ['error' => 'Invalid request type'];
    }

    switch ($request['type']) {
        case 'register':
            $username = $request['username'];
            $password = password_hash($request['password'], PASSWORD_DEFAULT);

            try {
                $stmt = $db->prepare("INSERT INTO accounts (username, password) VALUES (:username, :password)");
                $stmt->execute([':username' => $username, ':password' => $password]);
                return ['status' => 'success', 'message' => 'User registered'];
            } catch (PDOException $e) {
                return ['status' => 'error', 'message' => $e->getMessage()];
            }

        case 'login':
            $username = $request['username'];
            $password = $request['password'];

            try {
                $stmt = $db->prepare("SELECT * FROM accounts WHERE username = :username");
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    return ['status' => 'success', 'message' => 'Login successful'];
                } else {
                    return ['status' => 'error', 'message' => 'Invalid username or password'];
                }
            } catch (PDOException $e) {
                return ['status' => 'error', 'message' => $e->getMessage()];
            }

        case 'update_session':
            $username = $request['username'];
            $session_token = $request['session_token'];
            $session_expires = $request['session_expires'];

            try {
                $stmt = $db->prepare("UPDATE accounts SET session_token = :session_token, session_expires = :session_expires WHERE username = :username");
                $stmt->execute([':session_token' => $session_token, ':session_expires' => $session_expires, ':username' => $username]);
                return ['status' => 'success', 'message' => 'Session updated'];
            } catch (PDOException $e) {
                return ['status' => 'error', 'message' => $e->getMessage()];
            }

        default:
            return ['error' => 'Unknown request type'];
    }
}

// Initialize RabbitMQ listener
$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");

$server->process_requests('requestProcessor');
?>

