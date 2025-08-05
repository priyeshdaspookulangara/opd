<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Package.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate package object
$package = new Package($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->package_id) &&
    !empty($data->package_name) &&
    !empty($data->price) &&
    !empty($data->duration_days)
) {
    // Set ID to update
    $package->package_id = $data->package_id;

    $package->package_name = $data->package_name;
    $package->description = $data->description ?? '';
    $package->price = $data->price;
    $package->duration_days = $data->duration_days;
    $package->is_active = $data->is_active ?? true;

    // service_ids should be an array of integers
    $package->service_ids = $data->service_ids ?? [];

    // Update package
    if($package->update()) {
        http_response_code(200); // OK
        echo json_encode(
            array('message' => 'Package Updated')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Package Not Updated')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Package Not Updated. Incomplete data.')
    );
}
?>
