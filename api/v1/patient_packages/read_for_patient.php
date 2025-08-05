<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../config/database.php';
include_once '../models/PatientPackage.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate patient package object
$patient_package = new PatientPackage($db);

// Get patient ID from URL
$patient_package->patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : die();

// Get packages for patient
$result = $patient_package->read_for_patient();
$num = $result->rowCount();

if($num > 0) {
    $packages_arr = array();
    $packages_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $package_item = array(
            'patient_package_id' => $patient_package_id,
            'package_name' => $package_name,
            'description' => $description,
            'price' => $price,
            'start_date' => $start_date,
            'end_date' => $end_date
        );
        array_push($packages_arr['data'], $package_item);
    }
    echo json_encode($packages_arr);
} else {
    // No packages found for this patient
    echo json_encode(
        array('data' => array()) // Return empty data array
    );
}
?>
