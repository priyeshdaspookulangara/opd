<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Billing.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate billing object
$billing = new Billing($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->patient_id) &&
    !empty($data->service_id)
) {
    $billing->patient_id = $data->patient_id;
    $billing->service_id = $data->service_id;
    $billing->op_id = $data->op_id ?? null; // op_id is optional

    // Create billing entry
    if($billing->create()) {
        http_response_code(201); // 201 Created
        echo json_encode(
            array(
                'message' => 'Billing record created.',
                'amount_billed' => $billing->amount // Return the amount that was actually billed
            )
        );
    } else {
        http_response_code(503); // 503 Service Unavailable
        echo json_encode(
            array('message' => 'Billing record not created.')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // 400 Bad Request
    echo json_encode(
        array('message' => 'Billing record not created. Patient ID and Service ID are required.')
    );
}
?>
