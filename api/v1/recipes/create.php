<?php
// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../classes/recipes.php';
include_once '../classes/common.php';
include_once 'authentication.php';

$recipeObj = new Recipes();
$common = new Common();

// POST data
$data = $common->getPostData();
$result = [];
if (!empty($data['recipeName']) && !empty($data['prepTime']) && !empty($data['difficulty'])) {
    $response = $recipeObj->create($data);
   
    if ($response) {
        http_response_code(201);
        $result = [
            'code' => 201,
            'status' => true,
            'message' => 'Success',
            'data' => ['recipeId' => $response]
        ];
    } else {
        // 503 service unavailable
        http_response_code(503);
        $result = [
            'code' => 503,
            'status' => false,
            'message' => 'Failed to add data'
        ];
    }
} else {
    // 400 bad request
    http_response_code(400);
    $result = [
        'code' => 400,
        'status' => false,
        'message' => 'Incomlete post data'
    ];
}

echo json_encode($result);
