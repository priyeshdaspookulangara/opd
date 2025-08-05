<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
require_role('Admin');

include_once '../config/database.php';
include_once '../models/AvailableRadiologyTest.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate radiology test object
$radiology_test = new AvailableRadiologyTest($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->test_name) &&
    isset($data->cost)
) {
    $radiology_test->test_name = $data->test_name;
    $radiology_test->description = $data->description ?? '';
    $radiology_test->cost = $data->cost;
    $radiology_test->is_available = $data->is_available ?? true;

    // Create radiology test
    if($radiology_test->create()) {
        http_response_code(201); // Created
        echo json_encode(
            array('message' => 'Radiology Test Created')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Radiology Test Not Created')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Radiology Test Not Created. Test name and cost are required.')
    );
}
?>
