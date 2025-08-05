<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../config/database.php';
include_once '../models/Service.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate service object
$service = new Service($db);

// Service query
$result = $service->read();
// Get row count
$num = $result->rowCount();

// Check if any services
if($num > 0) {
    // Service array
    $services_arr = array();
    $services_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $service_item = array(
            'service_id' => $service_id,
            'service_name' => $service_name,
            'description' => $description,
            'cost' => $cost
        );

        // Push to "data"
        array_push($services_arr['data'], $service_item);
    }

    // Turn to JSON & output
    echo json_encode($services_arr);

} else {
    // No Services
    http_response_code(404);
    echo json_encode(
        array('message' => 'No Services Found')
    );
}
?>
