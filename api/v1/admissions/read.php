<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../config/database.php';
include_once '../models/Admission.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate admission object
$admission = new Admission($db);

// Admission query
$result = $admission->read();
// Get row count
$num = $result->rowCount();

// Check if any admissions
if ($num > 0) {
    // Admissions array
    $admissions_arr = array();
    $admissions_arr['data'] = array();

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $admission_item = array(
            'admission_id' => $admission_id,
            'patient_id' => $patient_id,
            'patient_name' => $patient_name, // Joined from patients table
            'bed_id' => $bed_id,
            'bed_number' => $bed_number,   // Joined from beds table
            'ward_name' => $ward_name,     // Joined from wards table
            'admission_date' => $admission_date,
            'discharge_date' => $discharge_date,
            'diagnosis' => $diagnosis
        );

        // Push to "data"
        array_push($admissions_arr['data'], $admission_item);
    }

    // Turn to JSON & output
    echo json_encode($admissions_arr);
} else {
    // No Admissions
    echo json_encode(
        array('message' => 'No Admissions Found')
    );
}
