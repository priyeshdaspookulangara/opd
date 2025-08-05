<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Admission.php';
include_once '../models/Bed.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate admission object
$admission = new Admission($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Check for required fields
if (
    !isset($data->admission_id) ||
    !isset($data->discharge_date)
) {
    // Bad request
    http_response_code(400);
    echo json_encode(
        array('message' => 'Error: Missing required fields (admission_id, discharge_date).')
    );
    return;
}

// Set admission properties
$admission->admission_id = $data->admission_id;
$admission->discharge_date = $data->discharge_date;

// We need the bed_id to update its status
$bed_id = $admission->getBedId();

if (!$bed_id) {
    http_response_code(404);
    echo json_encode(array('message' => 'Admission record not found.'));
    return;
}


// Update Admission (Discharge)
if ($admission->discharge()) {
    // If discharge is successful, mark the bed as not occupied
    $bed = new Bed($db);
    if ($bed->markAsNotOccupied($bed_id)) {
        echo json_encode(
            array('message' => 'Patient discharged successfully and bed has been freed.')
        );
    } else {
        http_response_code(207); // Multi-Status
        echo json_encode(
            array('message' => 'Patient discharged, but failed to update bed status.')
        );
    }
} else {
    // Internal server error
    http_response_code(500);
    echo json_encode(
        array('message' => 'Error: Patient could not be discharged.')
    );
}
