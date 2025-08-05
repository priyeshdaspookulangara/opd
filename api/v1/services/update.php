<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
require_role('Admin');

include_once '../config/database.php';
include_once '../models/Service.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate service object
$service = new Service($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->service_id) &&
    !empty($data->service_name) &&
    !empty($data->cost)
) {
    // Set ID to update
    $service->service_id = $data->service_id;

    $service->service_name = $data->service_name;
    $service->description = $data->description ?? '';
    $service->cost = $data->cost;

    // Update service
    if($service->update()) {
        http_response_code(200); // OK
        echo json_encode(
            array('message' => 'Service Updated')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Service Not Updated')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Service Not Updated. Incomplete data.')
    );
}
?>
