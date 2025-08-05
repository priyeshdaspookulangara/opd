<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../auth/validate_session.php';

include_once '../config/database.php';
include_once '../models/Bed.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate bed object
$bed = new Bed($db);

// Check for ward_id filter
if (isset($_GET['ward_id'])) {
    $bed->ward_id = $_GET['ward_id'];
}

// Bed query
$available_only = isset($_GET['available']) && $_GET['available'] == 'true';
$result = $bed->read($available_only);
$num = $result->rowCount();

if($num > 0) {
    $beds_arr = array();
    $beds_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $bed_item = array(
            'bed_id' => $bed_id,
            'bed_number' => $bed_number,
            'ward_name' => $ward_name,
            'is_occupied' => (bool)$is_occupied
        );
        array_push($beds_arr['data'], $bed_item);
    }
    echo json_encode($beds_arr);
} else {
    // No Beds
    echo json_encode(
        array('data' => array())
    );
}
?>
