<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
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

if (!empty($data->ward_name)) {
    $ward->ward_name = $data->ward_name;
    $ward->description = $data->description ?? '';

    // Create ward
    if($ward->create()) {
        http_response_code(201); // Created
        echo json_encode(
            array('message' => 'Ward Created')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Ward Not Created')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Ward Not Created. Ward name is required.')
    );
}
?>
