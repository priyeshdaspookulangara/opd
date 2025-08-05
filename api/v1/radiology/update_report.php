<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
// require_role('Radiologist');

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
    !empty($data->radiology_order_id) &&
    isset($data->report)
) {
    $radiology_order->radiology_order_id = $data->radiology_order_id;
    $radiology_order->report = $data->report;
    $radiology_order->notes = $data->notes ?? '';

    // Update report
    if($radiology_order->update_report()) {
        http_response_code(200); // OK
        echo json_encode(
            array('message' => 'Radiology Report Updated')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Radiology Report Not Updated')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Radiology Report Not Updated. Incomplete data.')
    );
}
?>
