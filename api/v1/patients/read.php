<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../config/database.php';
include_once '../models/Patient.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate patient object
$patient = new Patient($db);

// Patient query
$result = $patient->read();
// Get row count
$num = $result->rowCount();

// Check if any patients
if($num > 0) {
    // Patient array
    $patients_arr = array();
    $patients_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $patient_item = array(
            'patient_id' => $patient_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'date_of_birth' => $date_of_birth,
            'gender' => $gender,
            'phone_number' => $phone_number,
            'email' => $email,
            'address' => $address
        );

        // Push to "data"
        array_push($patients_arr['data'], $patient_item);
    }

    // Turn to JSON & output
    echo json_encode($patients_arr);

} else {
    // No Patients
    http_response_code(404); // Not Found
    echo json_encode(
        array('message' => 'No Patients Found')
    );
}
?>
