<?php
session_start(); // Use standard PHP session handling

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

// Clear session token from the database
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $stmt = $db->prepare("UPDATE accounts SET session_token = NULL, session_expires = NULL WHERE username = ?");
    $stmt->execute([$username]);
}

// Clear the session
session_unset();
session_destroy();

// Redirect to homepage
header("Location: index.php");
exit();
?>

