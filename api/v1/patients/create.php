<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/Patient.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate patient object
$patient = new Patient($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->first_name) &&
    !empty($data->last_name) &&
    (!empty($data->date_of_birth) || !empty($data->age)) && // Updated logic
    !empty($data->gender)
) {
    $patient->first_name = $data->first_name;
    $patient->last_name = $data->last_name;
    $patient->date_of_birth = $data->date_of_birth ?? null;
    $patient->age = $data->age ?? null; // Pass age
    $patient->gender = $data->gender;
    $patient->phone_number = $data->phone_number ?? null;
    $patient->email = $data->email ?? null;
    $patient->address = $data->address ?? null;

    // Create patient
    if($patient->create()) {
        http_response_code(201); // 201 Created
        echo json_encode(
            array('message' => 'Patient Created', 'patient_id' => $patient->patient_id)
        );
    } else {
        http_response_code(503); // 503 Service Unavailable
        echo json_encode(
            array('message' => 'Patient Not Created')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // 400 Bad Request
    echo json_encode(
        array('message' => 'Patient Not Created. Incomplete data. First name, last name, gender, and either DOB or age are required.')
    );
}
?>
