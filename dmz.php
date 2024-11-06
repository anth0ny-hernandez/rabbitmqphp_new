#!/bin/php

<?php
require_once('rabbitMQLib.inc');
require_once('get_host_info.inc');
require_once('path.inc');
$conn = new mysqli('localhost', 'testUser', '12345', 'testdb');

function dmzProcessor($request){
    $conn = new mysqli('localhost', 'testUser', '12345', 'testdb');

$response = array();
echo "Received request: ";
// var_dump($request);


//parameters to append to endpoint url
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


// $conn = new mysqli('localhost', 'testUser', '12345', 'testdb');

//do api call (GET) with appropriate paremeters

$cu = curl_init();
$url = "https://api.edamam.com/api/recipes/v2?". http_build_query($params);
echo($url);
curl_setopt($cu, CURLOPT_URL, "https://api.edamam.com/api/recipes/v2?". http_build_query($params));
curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);

//alvees username header
$headers = [
    'Edamam-Account-User: AlveeJalal',
    
];

curl_setopt($cu, CURLOPT_HTTPHEADER, $headers);

$data = curl_exec($cu);

//close the call
curl_close($cu);

//convert from json to array
$data2 =json_decode($data, true);
// var_dump($data2);
// var_dump($data);
// $data3 = json_encode($data2, JSON_PRETTY_PRINT);
// var_dump($data3);

// var_dump($cu);


//logic to send it back to rmq which sends to db. Access nested indices, then send back to rmq as a query to insert into DB.

// switch ($request['type']) {
    
//     case "insertRecipe":
        foreach($data2['hits'] as $hit)
        {
        $recipe = $hit['recipe'];

        $recipeName = $recipe['label'];
        $image = $recipe['image'];
        $url = $recipe['url'];
        $healthLabels = implode(',', $recipe['healthLabels']);
        $energy = $recipe['totalNutrients']['ENERC_KCAL']['quantity'];
        $ingredients = implode(',', $recipe['ingredientLines']);
        $calories = $recipe['calories'];
        $cuisineType = implode(',', $recipe['cuisineType']);
        $mealType = implode(',', $recipe['mealType']);
        $fat = $recipe['totalNutrients']['FAT']['quantity'];
        $carbs = $recipe['totalNutrients']['CHOCDF']['quantity'];
        $fiber = $recipe['totalNutrients']['FIBTG']['quantity'];
        $sugar = $recipe['totalNutrients']['SUGAR']['quantity'];
        $protein = $recipe['totalNutrients']['PROCNT']['quantity'];
        $cholesterol = $recipe['totalNutrients']['CHOLE']['quantity'];
        $sodium = $recipe['totalNutrients']['NA']['quantity'];
        $calcium = $recipe['totalNutrients']['CA']['quantity'];
        $vitaminA = $recipe['totalNutrients']['VITA_RAE']['quantity'];
        $vitaminC = $recipe['totalNutrients']['VITC']['quantity'];

        $time = time();

            $queryStatement = "INSERT INTO recipes (label, image, url, healthLabels, 
            ENERC_KCAL, ingredientLines, calories, cuisineType, 
            mealType, fat, carbs, fiber, sugars, protein, 
            cholesterol, sodium, calcium, vitaminA, vitaminC, timestamp)
        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ? )";
$query = $conn->prepare($queryStatement);
$query->bind_param("ssssisissiiiiiiiiiii", 
$recipeName, $image, $url, $healthLabels, 
$energy, $ingredients, $calories, $cuisineType, 
$mealType, $fat, $carbs, $fiber, $sugar, $protein, 
$cholesterol, $sodium, $calcium, $vitaminA, 
$vitaminC, $time);

if ($query->execute()) {
echo "Recipe(s) inserted successfully!\n";
echo "================================\n";
// $recipesArray = selectRecipes($request, $conn); // uses function akin to searchRecipe case
// // $response['query'] = $queryStatement;
// // echo $response['query'];
// return $recipesArray;
} else {
//Log and return the error
error_log("Error in registration: " . $conn->error);
echo "Error: " . $conn->error . "\n";
// $insert = "Error: " . $conn->error;
return false;
}  

        //check if recipes already in db. if already, send a message. if not, do insert query 
    //     $checkQuery = "SELECT * FROM recipes  WHERE label = ?";
    //     $stmt = $conn->prepare($checkQuery);
    //     $stmt->bind_param("s", $recipeName);
    //     $stmt->execute();
    //     $ray = $stmt->get_result();
            
    //     if ($ray->num_rows > 0) 
    //    {
    //        $response['msg'] = "Recipes exist already. No need to insert"; 
    //    }
    



        //if not, insert into db
                // $queryStatement = "INSERT INTO recipes (label, image, url, healthLabels, ENERC_KCAL, ingredientLines, calories, cuisineType, mealType, fat, carbs, fiber, sugars, protein, cholesterol, sodium, calcium, vitaminA, vitaminC, timestamp)
                // values ($recipeName, $image, $url, $healthLabels, $energy, $ingredients, $calories, $cuisineType, $mealType, $fat, $carbs, $fiber, $sugar, $protein, $cholesterol, $sodium, $calcium, $vitaminA, $vitaminC, $time)";
                //values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
                // $query = $conn->prepare($queryStatement);
            // $query->bind_param("ssssisissiiiiiiiiiii",  $recipeName, $image, $url, $healthLabels, $energy, $ingredients, $calories, $cuisineType, $mealType, $fat, $carbs, $fiber, $sugar, $protein, $cholesterol, $sodium, $calcium, $vitaminA, $vitaminC, $time);
            // $query->execute();

            // $response['query'] = $queryStatement;
            // echo $response['query'];

            $response['label'] = $recipeName;
            $response['image'] = $image;
            $response['url'] = $url;
            $response['healthLabels'] = $healthLabels;
            $response['ENERC_KCAL'] = $energy;
            $response['ingredientLines'] = $ingredients;
            $response['calories'] = $calories;
            $response['cuisineType'] = $cuisineType;
            $response['mealType'] = $mealType;
            $response['FAT'] = $fat;
            $response['carbs'] = $carbs;
            $response['fiber'] = $fiber;
            $response['sugar'] = $sugar;
            $response['protein'] = $protein;
            $response['cholesterol'] =  $cholesterol;
            $response['sodium'] = $sodium;
            $response['calcium'] = $calcium;
            $response['vitaminA'] = $vitaminA;
            $response['vitaminC'] = $vitaminC;


            }      
            //send api data as a array to db so it can use it to insert
            
        return $response;
//     default:
//             return "ERROR: unsupported message type";
// }

}


dmzProcessor(array("label"=>"salad"));

$dmzServer = new rabbitMQServer("testDMZ_RMQ.ini", "testDMZ");
echo "DMZ Server is running and waiting for requests...\n";
$dmzServer->process_requests('dmzProcessor');

?>