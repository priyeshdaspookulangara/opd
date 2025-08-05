<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
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
    !isset($data->patient_id) ||
    !isset($data->bed_id) ||
    !isset($data->admission_date)
) {
    // Bad request
    http_response_code(400);
    echo json_encode(
        array('message' => 'Error: Missing required fields (patient_id, bed_id, admission_date).')
    );
    return;
}

$admission->patient_id = $data->patient_id;
$admission->bed_id = $data->bed_id;
$admission->admission_date = $data->admission_date;
$admission->discharge_date = isset($data->discharge_date) ? $data->discharge_date : null;
$admission->diagnosis = isset($data->diagnosis) ? $data->diagnosis : null;

// Create Admission
if ($admission->create()) {
    // If admission is successful, mark the bed as occupied
    $bed = new Bed($db);
    if ($bed->markAsOccupied($admission->bed_id)) {
        echo json_encode(
            array('message' => 'Patient admitted successfully and bed status updated.')
        );
    } else {
        // This case is tricky. The admission was created, but the bed status failed to update.
        // For now, we'll report a mixed success. A more robust system might roll back the admission.
        http_response_code(207); // Multi-Status
        echo json_encode(
            array('message' => 'Patient admitted, but failed to update bed status.')
        );
    }
} else {
    // Internal server error
    http_response_code(500);
    echo json_encode(
        array('message' => 'Error: Patient could not be admitted.')
    );
}
