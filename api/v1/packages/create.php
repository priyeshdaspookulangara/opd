<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
require_role('Admin');

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
    !empty($data->package_name) &&
    !empty($data->price) &&
    !empty($data->duration_days)
) {
    $package->package_name = $data->package_name;
    $package->description = $data->description ?? '';
    $package->price = $data->price;
    $package->duration_days = $data->duration_days;
    $package->is_active = $data->is_active ?? true;

    // service_ids should be an array of integers
    $package->service_ids = $data->service_ids ?? [];

    // Create package
    if($package->create()) {
        http_response_code(201); // 201 Created
        echo json_encode(
            array('message' => 'Package Created')
        );
    } else {
        http_response_code(503); // 503 Service Unavailable
        echo json_encode(
            array('message' => 'Package Not Created')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // 400 Bad Request
    echo json_encode(
        array('message' => 'Package Not Created. Package name, price, and duration are required.')
    );
}
?>
