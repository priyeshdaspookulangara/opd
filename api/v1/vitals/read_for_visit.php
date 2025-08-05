<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../auth/validate_session.php';

include_once '../config/database.php';
include_once '../models/Vital.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate vital object
$vital = new Vital($db);

// Get op_id from URL
$vital->op_id = isset($_GET['op_id']) ? $_GET['op_id'] : die();

// Get vitals for visit
$result = $vital->read_for_visit();
$num = $result->rowCount();

if($num > 0) {
    // Since we limit to 1, we don't need a while loop
    $row = $result->fetch(PDO::FETCH_ASSOC);
    extract($row);
    $vital_item = array(
        'vital_id' => $vital_id,
        'temperature' => $temperature,
        'blood_pressure' => $blood_pressure,
        'heart_rate' => $heart_rate,
        'respiratory_rate' => $respiratory_rate,
        'recorded_at' => $recorded_at
    );
    echo json_encode($vital_item);
} else {
    // No vitals found for this visit
    http_response_code(404);
    echo json_encode(
        array('message' => 'No vitals found for this visit.')
    );
}
?>
