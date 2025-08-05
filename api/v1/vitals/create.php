<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Vital.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate vital object
$vital = new Vital($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->op_id) &&
    isset($data->temperature) &&
    isset($data->blood_pressure) &&
    isset($data->heart_rate) &&
    isset($data->respiratory_rate)
) {
    $vital->op_id = $data->op_id;
    $vital->temperature = $data->temperature;
    $vital->blood_pressure = $data->blood_pressure;
    $vital->heart_rate = $data->heart_rate;
    $vital->respiratory_rate = $data->respiratory_rate;

    // Create vital
    if($vital->create()) {
        http_response_code(201); // 201 Created
        echo json_encode(
            array('message' => 'Vitals Recorded')
        );
    } else {
        http_response_code(503); // 503 Service Unavailable
        echo json_encode(
            array('message' => 'Vitals Not Recorded')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // 400 Bad Request
    echo json_encode(
        array('message' => 'Vitals Not Recorded. Incomplete data.')
    );
}
?>
