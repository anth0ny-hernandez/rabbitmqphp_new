#!/bin/php

<?php
//need logic to process requests from frontend, then need to place in database and response back to frontend. 
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');

function dmzProcessor($request){
echo "Received request: ";
// var_dump($request);

$params = array(
'type'=>'public'?? null,
'q'=>$request['label'] ?? null, 
'app_id'=>'4577783c', 
'app_key'=>'2ebd6b0aa43312e5f01f2077882ca32f',
'health'=>$request['healthLabels'] ?? null,
'cuisineType'=>$request['cuisineType'] ?? null,
'mealType'=>$request['mealType'] ?? null,
'nutrients[ENERC_KCAL]'=>$request['ENERC_KCAL'] ?? null,
'nutrients[CA]'=>$request['calcium'] ?? null,
'nutrients[CHOCDF]'=>$request['carbohydrate'] ?? null,
'nutrients[CHOLE]'=>$request['cholesterol'] ?? null,
'nutrients[FAT]'=>$request['fat'] ?? null,
'nutrients[FIBTF]'=>$request['fiber'] ?? null,
'nutrients[NA]'=>$request['sodium'] ?? null,
'nutrients[PROCNT]'=>$request['protein'] ?? null,
'nutrients[SUGAR]'=>$request['sugar'] ?? null,
'nutrients[VITA_RAE]'=>$request['vitaminA'] ?? null,
'nutrients[VITC]'=>$request['vitaminC'] ?? null,
'ingredientLines'=>$request['ingredients'] ?? null,
);

// if($request['healthLabels'])
// {
//   $params['health'] = $request['healthLabels'];
// }


// if($request['cuisineType'])
// {
//   $params['cuisineType'] = $request['cuisineType'];
// }


// if($request['mealType'])
// {
//   $params['mealType'] = $request['mealType'];
// }


// if($request['ENERC_KCAL'])
// {
//   $params['nutrients[ENERC_KCAL]'] = $request['ENERC_KCAL'];
// }


// if($request['calcium'])
// {
//   $params['nutrients[CA]'] = $request['calcium'];
// }


// if($request['carbohydrate'])
// {
//   $params['nutrients[CHOCDF]'] = $request['carbohydrate'];
// }


// if($request['cholesterol'])
// {
//   $params['nutrients[CHOLE]'] = $request['cholesterol'];
// }

// if($request['fat'])
// {
//   $params['nutrients[FAT]'] = $request['fat'];
// }

// if($request['fiber'])
// {
//   $params['nutrients[FIBTF]'] = $request['fiber'];
// }

// if($request['sodium'])
// {
//   $params['nutrients[NA]'] = $request['sodium'];
// }

// if($request['protein'])
// {
//   $params['nutrients[PROCNT]'] = $request['protein'];
// }

// if($request['sugar'])
// {
//   $params['nutrients[SUGAR]'] = $request['sugar'];
// }

// if($request['vitaminA'])
// {
//   $params['nutrients[VITA_RAE]'] = $request['vitaminA'];
// }

// if($request['vitaminC'])
// {
//   $params['nutrients[VITC]'] = $request['vitaminC'];
// }
// if($request['ingredients'])
// {
//   $params['ingredientLines'] = $request['ingredients'];
// }



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

$data2 =json_decode($data, true);
// var_dump($data2);
// var_dump($data);
// $data3 = json_encode($data2, JSON_PRETTY_PRINT);
// var_dump($data3);

// var_dump($cu);


//logic to send it back to rmq which sends to db. Access nested indices, then send back to rmq as a query to insert into DB.


foreach($data2['hits'] as $hit)
{
    $recipe = $hit['recipe'];

    $recipeName = $recipe['label'];

}
}

$request = array('label'=> 'chicken', 'sodium'=>"1600+");

dmzProcessor($request);
?>