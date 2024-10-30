#!/bin/php

<?php
//need logic to process requests from frontend, then need to place in database and response back to frontend. 

$cu = curl_init();

curl_setopt($cu, CURLOPT_URL, "https://api.edamam.com/api/recipes/v2?type=public&q=teriyaki&app_id=4577783c&app_key=2ebd6b0aa43312e5f01f2077882ca32f&nutrients%5BENERC_KCAL%5D=1030&nutrients%5BFAMS%5D=50&nutrients%5BSUGAR%5D=21");
curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);

$headers = [
    'Edamam-Account-User: AlveeJalal',
    
];

curl_setopt($cu, CURLOPT_HTTPHEADER, $headers);

$data = curl_exec($cu);

curl_close($cu);

$data2 =json_decode($data);
var_dump($data2);
var_dump($data);

?>