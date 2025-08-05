<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
// require_role('Lab Technician');

include_once '../config/database.php';
include_once '../models/LabTest.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate lab test object
$lab_test = new LabTest($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->lab_test_id) &&
    isset($data->result)
) {
    $lab_test->lab_test_id = $data->lab_test_id;
    $lab_test->result = $data->result;
    $lab_test->notes = $data->notes ?? '';

    // Update result
    if($lab_test->update_result()) {
        http_response_code(200); // OK
        echo json_encode(
            array('message' => 'Lab Test Result Updated')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Lab Test Result Not Updated')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Lab Test Result Not Updated. Incomplete data.')
    );
}
?>
