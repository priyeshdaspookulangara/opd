<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../auth/validate_session.php';

include_once '../config/database.php';
include_once '../models/Ward.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate ward object
$ward = new Ward($db);

// Ward query
$result = $ward->read();
$num = $result->rowCount();

if($num > 0) {
    $wards_arr = array();
    $wards_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $ward_item = array(
            'ward_id' => $ward_id,
            'ward_name' => $ward_name,
            'description' => $description
        );
        array_push($wards_arr['data'], $ward_item);
    }
    echo json_encode($wards_arr);
} else {
    // No Wards
    echo json_encode(
        array('data' => array())
    );
}
?>
