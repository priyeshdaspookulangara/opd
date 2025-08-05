<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
require_role('Admin');

include_once '../config/database.php';
include_once '../models/Ward.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate ward object
$ward = new Ward($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->ward_id) &&
    !empty($data->ward_name)
) {
    $ward->ward_id = $data->ward_id;
    $ward->ward_name = $data->ward_name;
    $ward->description = $data->description ?? '';

    // Update ward
    if($ward->update()) {
        http_response_code(200); // OK
        echo json_encode(
            array('message' => 'Ward Updated')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Ward Not Updated')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Ward Not Updated. Incomplete data.')
    );
}
?>
