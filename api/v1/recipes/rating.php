<?php
// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../classes/recipes.php';
include_once '../classes/common.php';

$recipeObj = new Recipes();
$common = new Common();

// POST data
$data = $common->getPostData();
$result = [];
if (!empty($data['recipeId']) && !empty($data['rating']) ) {
    $response = $recipeObj->addRating($data);
    
    if ($response) {
        http_response_code(201);
        $result = [
            'code' => 201,
            'status' => true,
            'message' => 'Success',
            'data' => ['ratingId' => $response]
        ];
    } else {
        // 503 service unavailable
        http_response_code(503);
        $result = [
            'code' => 503,
            'status' => false,
            'message' => 'Failed to update data'
        ];
    }
} else {
    http_response_code(400);
    $result = [
        'code' => 400,
        'status' => false,
        'message' => 'Isufficient input parameters'
    ];
}

echo json_encode($result);
