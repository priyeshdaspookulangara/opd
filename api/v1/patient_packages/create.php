<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/PatientPackage.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate patient package object
$patient_package = new PatientPackage($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->patient_id) &&
    !empty($data->package_id)
) {
    $patient_package->patient_id = $data->patient_id;
    $patient_package->package_id = $data->package_id;

    // Create patient package
    if($patient_package->create()) {
        http_response_code(201); // 201 Created
        echo json_encode(
            array('message' => 'Package assigned to patient.')
        );
    } else {
        http_response_code(503); // 503 Service Unavailable
        echo json_encode(
            array('message' => 'Package could not be assigned.')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // 400 Bad Request
    echo json_encode(
        array('message' => 'Package could not be assigned. Patient ID and Package ID are required.')
    );
}
?>
