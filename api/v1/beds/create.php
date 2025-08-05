<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
require_role('Admin');

include_once '../config/database.php';
include_once '../models/Bed.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate bed object
$bed = new Bed($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->bed_number) &&
    !empty($data->ward_id)
) {
    $bed->bed_number = $data->bed_number;
    $bed->ward_id = $data->ward_id;
    $bed->is_occupied = $data->is_occupied ?? false;

    // Create bed
    if($bed->create()) {
        http_response_code(201); // Created
        echo json_encode(
            array('message' => 'Bed Created')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Bed Not Created')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Bed Not Created. Bed number and ward ID are required.')
    );
}
?>
