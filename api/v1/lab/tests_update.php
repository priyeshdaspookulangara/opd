<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
require_role('Admin');

include_once '../config/database.php';
include_once '../models/AvailableLabTest.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate lab test object
$lab_test = new AvailableLabTest($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->available_test_id) &&
    !empty($data->test_name) &&
    isset($data->cost)
) {
    $lab_test->available_test_id = $data->available_test_id;
    $lab_test->test_name = $data->test_name;
    $lab_test->description = $data->description ?? '';
    $lab_test->cost = $data->cost;
    $lab_test->is_available = $data->is_available ?? true;

    // Update lab test
    if($lab_test->update()) {
        http_response_code(200); // OK
        echo json_encode(
            array('message' => 'Lab Test Updated')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Lab Test Not Updated')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Lab Test Not Updated. Incomplete data.')
    );
}
?>
