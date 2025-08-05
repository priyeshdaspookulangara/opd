<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../auth/validate_session.php';

include_once '../config/database.php';
include_once '../models/AvailableLabTest.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate lab test object
$lab_test = new AvailableLabTest($db);

// Lab test query
$result = $lab_test->read();
$num = $result->rowCount();

if($num > 0) {
    $tests_arr = array();
    $tests_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $test_item = array(
            'available_test_id' => $available_test_id,
            'test_name' => $test_name,
            'description' => $description,
            'cost' => $cost,
            'is_available' => $is_available
        );
        array_push($tests_arr['data'], $test_item);
    }
    echo json_encode($tests_arr);
} else {
    // No Tests
    echo json_encode(
        array('data' => array())
    );
}
?>
