<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../config/database.php';
include_once '../models/Service.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate service object
$service = new Service($db);

// Get ID from URL
$service->service_id = isset($_GET['id']) ? $_GET['id'] : die();

// Get service
if($service->read_one()) {
    // Create array
    $service_arr = array(
        'service_id' => $service->service_id,
        'service_name' => $service->service_name,
        'description' => $service->description,
        'cost' => $service->cost
    );

    // Make JSON
    http_response_code(200); // OK
    echo json_encode($service_arr);
} else {
    // Not found
    http_response_code(404); // Not Found
    echo json_encode(array('message' => 'Service not found.'));
}
?>
