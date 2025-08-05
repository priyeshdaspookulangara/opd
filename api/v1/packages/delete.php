<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
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

if (!empty($data->package_id)) {
    // Set ID to delete
    $package->package_id = $data->package_id;

    // Delete package
    if($package->delete()) {
        http_response_code(200); // OK
        echo json_encode(
            array('message' => 'Package Deleted')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Package Not Deleted')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Package Not Deleted. No ID provided.')
    );
}
?>
