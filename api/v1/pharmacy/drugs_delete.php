<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../auth/validate_session.php';
require_role('Admin');

include_once '../config/database.php';
include_once '../models/Drug.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate drug object
$drug = new Drug($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->drug_id)) {
    $drug->drug_id = $data->drug_id;

    // Delete drug
    if($drug->delete()) {
        http_response_code(200); // OK
        echo json_encode(
            array('message' => 'Drug Deleted')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'Drug Not Deleted')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Drug Not Deleted. No ID provided.')
    );
}
?>
