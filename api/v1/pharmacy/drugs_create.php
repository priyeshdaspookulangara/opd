<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
require_role('Admin');

include_once '../config/database.php';
include_once '../models/Drug.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate drug object
$drug = new Drug($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->drug_name) &&
    isset($data->cost_per_unit) &&
    isset($data->stock_quantity)
) {
    $drug->drug_name = $data->drug_name;
    $drug->description = $data->description ?? '';
    $drug->cost_per_unit = $data->cost_per_unit;
    $drug->stock_quantity = $data->stock_quantity;

    // Create drug
    if($drug->create()) {
        http_response_code(201); // Created
        echo json_encode(
            array('message' => 'Drug Created')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Drug Not Created')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Drug Not Created. Drug name, cost, and stock are required.')
    );
}
?>
