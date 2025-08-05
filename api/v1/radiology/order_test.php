<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
require_role('Doctor');

include_once '../config/database.php';
include_once '../models/RadiologyOrder.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate radiology order object
$radiology_order = new RadiologyOrder($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->op_id) &&
    !empty($data->patient_id) &&
    !empty($data->available_test_id)
) {
    $radiology_order->op_id = $data->op_id;
    $radiology_order->patient_id = $data->patient_id;
    $radiology_order->available_test_id = $data->available_test_id;

    // Order radiology test
    if($radiology_order->order()) {
        http_response_code(201); // Created
        echo json_encode(
            array('message' => 'Radiology test ordered and billed successfully.')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Radiology test could not be ordered.')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Radiology test order failed. Incomplete data.')
    );
}
?>
