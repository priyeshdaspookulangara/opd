<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../config/database.php';
include_once '../models/Package.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate package object
$package = new Package($db);

// Get ID from URL
$package->package_id = isset($_GET['id']) ? $_GET['id'] : die();

// Get package
if($package->read_one()) {
    // Create array
    $package_arr = array(
        'package_id' => $package->package_id,
        'package_name' => $package->package_name,
        'description' => $package->description,
        'price' => $package->price,
        'duration_days' => $package->duration_days,
        'is_active' => $package->is_active,
        'service_ids' => $package->service_ids
    );

    // Make JSON
    http_response_code(200); // OK
    echo json_encode($package_arr);
} else {
    // Not found
    http_response_code(404); // Not Found
    echo json_encode(array('message' => 'Package not found.'));
}
?>
