<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
// We should check if the logged in user is a doctor
require_role('Doctor');

include_once '../config/database.php';
include_once '../models/Consultation.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate consultation object
$consultation = new Consultation($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->op_id) &&
    !empty($data->diagnosis) &&
    !empty($data->prescription)
) {
    $consultation->op_id = $data->op_id;
    $consultation->diagnosis = $data->diagnosis;
    $consultation->prescription = $data->prescription;
    $consultation->notes = $data->notes ?? '';

    // The doctor_id should come from the session
    $consultation->doctor_id = $_SESSION['user_id'];

    // Create consultation
    if($consultation->create()) {
        http_response_code(201); // Created
        echo json_encode(
            array('message' => 'Consultation Saved.')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Consultation Not Saved.')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Consultation not saved. OP ID, diagnosis, and prescription are required.')
    );
}
?>
