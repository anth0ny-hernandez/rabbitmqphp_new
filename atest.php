<?php
// Include the LogProd.php file
require_once 'LogProd.php';

// Simulate an error and log it
$errorMessage = "Error occurred in another file!";
logErrorAndSend($errorMessage);
?>
