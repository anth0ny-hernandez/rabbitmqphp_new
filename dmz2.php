#!/bin/php

<?php
//need logic to process requests from frontend, then need to place in database and response back to frontend. 
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

function requestProcessor($request){
echo "Received request: ";
// var_dump($request);

$params = array(
'type'=>'public', 
'q'=>$request, 
'app_id'=>'4577783c', 
'app_key'=>'2ebd6b0aa43312e5f01f2077882ca32f',
// 'health'=>$request['healthLabels'],
// 'cuisineType'=>$request['cuisineType'],
// 'mealType'=>$request['mealType'],
// 'nutrients[ENERC_KCAL]'=>$request['ENERC_KCAL'],
// 'nutrients[CA]'=>$request['calcium'],
// 'nutrients[CHOCDF]'=>$request['c'],
// 'nutrients[CHOLE]'=>$request['cholesterol'],
// 'nutrients[FAT]'=>$request['fat'],
// 'nutrients[FIBTF]'=>$request['fiber'],
// 'nutrients[NA]'=>$request['sodium'],
// 'nutrients[PROCNT]'=>$request['protein'],
// 'nutrients[SUGAR]'=>$request['sugar'],
// 'nutrients[VITA_RAE]'=>$request['vitaminA'],
// 'nutrients[VITC]'=>$request['vitaminCs'],
// 'ingredientLines'=>$request['ingredients'],
);


// $params = array(
// 'type'=>'public', 
// 'q'=>'teriyaki', 
// 'app_id'=>'4577783c', 
// 'app_key'=>'2ebd6b0aa43312e5f01f2077882ca32f',
// 'health'=>
// 'cuisineType'=>
// 'mealType'=>
// 'calories'=>
// 'nutrients[CA]'=>'1030+',
// 'nutrients[CHOCDF]'=>'1030+',
// 'nutrients[CHOLE]'=>'1030+',
// 'nutrients[ENERC_KCAL]'=>'1030+',
// 'nutrients[FAT]'=>'1030+',
// 'nutrients[FIBTF]'=>'1030+',
// 'nutrients[NA]'=>'1030+',
// 'nutrients[PROCNT]'=>'1030+',
// 'nutrients[SUGAR]'=>'35+',
// 'nutrients[VITA_RAE]'=>'35+',
// 'nutrients[VITC]'=>'35+'
// );

$cu = curl_init();
$url = "https://api.edamam.com/api/recipes/v2?". http_build_query($params);
echo($url);
curl_setopt($cu, CURLOPT_URL, "https://api.edamam.com/api/recipes/v2?". http_build_query($params));
curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);

$headers = [
    'Edamam-Account-User: AlveeJalal',
    
];

curl_setopt($cu, CURLOPT_HTTPHEADER, $headers);

$data = curl_exec($cu);

curl_close($cu);

// $data2 =
return json_decode($data);
// var_dump($data2);
// var_dump($data);
// var_dump($cu);
}
$server = new rabbitMQServer("dmzConfig.ini", "dmzServer");
echo "DMZ Server for Meal Planning and Recipe Search is running and waiting for requests...\n";
$server->process_requests('requestProcessor');
?>