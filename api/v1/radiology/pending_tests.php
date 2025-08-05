<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../auth/validate_session.php';
// require_role('Radiologist'); // In a real app

include_once '../config/database.php';
include_once '../models/RadiologyOrder.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate radiology order object
$radiology_order = new RadiologyOrder($db);

// Pending radiology test query
$result = $radiology_order->read_pending();
$num = $result->rowCount();

if($num > 0) {
    $tests_arr = array();
    $tests_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $test_item = array(
            'radiology_order_id' => $radiology_order_id,
            'op_id' => $op_id,
            'patient_name' => $first_name . ' ' . $last_name,
            'test_name' => $test_name,
            'status' => $status,
            'order_date' => $order_date
        );
        array_push($tests_arr['data'], $test_item);
    }
    echo json_encode($tests_arr);
} else {
    // No Pending Tests
    echo json_encode(
        array('data' => array())
    );
}
?>
