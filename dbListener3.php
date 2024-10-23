#!/bin/php
<?php
ob_start();
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');





// Create a server that listens for requests from clients
$dbClient = new rabbitMQClient("testDB_RMQ.ini", "dbConnect");
// Prepare the request
$request = array();
$request['type'] = "";
$request['username'] = null;
$request['password'] = null;

// Send the request and get the response
$response = $dbClient->send_request($request);

ob_end_flush();

// Close the database connection
$conn->close();

?>