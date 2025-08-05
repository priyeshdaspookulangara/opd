<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
// This could be restricted to 'Doctor' or 'Pharmacist'
// require_role('Doctor');

include_once '../config/database.php';
include_once '../models/Dispensation.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate dispensation object
$dispensation = new Dispensation($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->op_id) &&
    !empty($data->patient_id) &&
    !empty($data->drug_id) &&
    !empty($data->quantity) && $data->quantity > 0
) {
    $dispensation->op_id = $data->op_id;
    $dispensation->patient_id = $data->patient_id;
    $dispensation->drug_id = $data->drug_id;
    $dispensation->quantity = $data->quantity;

    // Create dispensation
    if($dispensation->create()) {
        http_response_code(201); // Created
        echo json_encode(
            array('message' => 'Drug dispensed and billed successfully.')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Drug could not be dispensed.')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Dispensation failed. Incomplete or invalid data.')
    );
}
?>
