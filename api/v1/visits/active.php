<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../auth/validate_session.php';
// In a more advanced implementation, you might check for 'Doctor' or 'Front Desk' roles
// require_role('Doctor');

include_once '../config/database.php';
include_once '../models/Visit.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate visit object
$visit = new Visit($db);

// Get active queue
$result = $visit->read_active_queue();
$num = $result->rowCount();

if($num > 0) {
    $queue_arr = array();
    $queue_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $queue_item = array(
            'op_id' => $op_id,
            'op_number' => $op_number,
            'patient_id' => $patient_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'visit_date' => $visit_date
        );
        array_push($queue_arr['data'], $queue_item);
    }
    echo json_encode($queue_arr);
} else {
    // No patients in queue
    echo json_encode(
        array('data' => array()) // Return empty data array
    );
}
?>
