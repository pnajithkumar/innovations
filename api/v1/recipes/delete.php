<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../classes/recipes.php';
include_once '../classes/common.php';
include_once 'authentication.php';

$recipeObj = new Recipes();
$common = new Common();
$data['id'] = $_GET['id']?$_GET['id']:'';
$result = [];
if (! empty($data['id'])) {

    $response = $recipeObj->delete($data);

    if ($response) {
        http_response_code(201);
        $result = [
            'code' => 201,
            'status' => true,
            'message' => 'Success'
        ];
    } else {
        // 503 service unavailable
        http_response_code(503);
        $result = [
            'code' => 503,
            'status' => false,
            'message' => 'Failed to delete the data for the given id'
        ];
    }
} else {
    // 400 bad request
    http_response_code(400);
    $result = [
        'code' => 400,
        'status' => false,
        'message' => 'Input is empty!'
    ];
}

echo json_encode($result);