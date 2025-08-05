<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Visit.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate visit object
$visit = new Visit($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->patient_id)) {
    $visit->patient_id = $data->patient_id;

    // Create visit
    if($visit->create()) {
        http_response_code(201); // 201 Created
        echo json_encode(
            array(
                'message' => 'Visit Created',
                'op_id' => $visit->op_id,
                'op_number' => $visit->op_number
            )
        );
    } else {
        http_response_code(503); // 503 Service Unavailable
        echo json_encode(
            array('message' => 'Visit Not Created')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // 400 Bad Request
    echo json_encode(
        array('message' => 'Visit Not Created. Patient ID is required.')
    );
}
?>
