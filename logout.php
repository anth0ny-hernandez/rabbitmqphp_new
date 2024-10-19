<?php
// Database connection (updated with new credentials)
// $dbHost = '172.22.53.55';
// $dbName = 'testdb';
// $dbUser = 'anthonyhz';
// $dbPassword = 'password';

// try {
//     $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
//     $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die("Connection failed: " . $e->getMessage());
// }

// Clear session token from the database if the session_token cookie is set
if (isset($_COOKIE['session_token'])) {
    $session_token = $_COOKIE['session_token'];
    
    // Clear the session token in the database
    $stmt = $db->prepare("UPDATE accounts SET session_token = NULL, session_expires = NULL WHERE session_token = ?");
    $stmt->execute([$session_token]);

    // Remove the session token cookie by setting its expiration to a past time
    setcookie('session_token', '', time() - 3600, "/");
}

// Redirect to homepage after logout
header("Location: index.php");
exit();
?>

