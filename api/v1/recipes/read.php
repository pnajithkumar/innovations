<?php
// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../classes/recipes.php';
include_once '../classes/common.php';

$recipeObj = new Recipes();
$common = new Common();

// POST data
$id = $_GET['id']?$_GET['id']:'';
$result = [];
    list($response, $rowCount) = $recipeObj->read($id);
    if ($rowCount) {
        http_response_code(201);
        $result = [
            'code' => 200,
            'status' => true,
            'message' => 'Success',
            'data' => $response
        ];
    } else {
        // 404 not found
        http_response_code(404);
        $result = [
            'code' => 404,
            'status' => false,
            'message' => 'No records found'
        ];
    }

echo json_encode($result);
