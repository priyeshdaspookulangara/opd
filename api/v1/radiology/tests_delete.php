<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
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

if (!empty($data->available_test_id)) {
    $radiology_test->available_test_id = $data->available_test_id;

    // Delete radiology test (soft delete)
    if($radiology_test->delete()) {
        http_response_code(200); // OK
        echo json_encode(
            array('message' => 'Radiology Test Deactivated')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Radiology Test Not Deactivated')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Radiology Test Not Deactivated. No ID provided.')
    );
}
?>
