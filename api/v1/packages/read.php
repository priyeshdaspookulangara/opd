<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../config/database.php';
include_once '../models/Package.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate package object
$package = new Package($db);

// Package query
$result = $package->read();
// Get row count
$num = $result->rowCount();

// Check if any packages
if($num > 0) {
    // Package array
    $packages_arr = array();
    $packages_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $package_item = array(
            'package_id' => $package_id,
            'package_name' => $package_name,
            'description' => $description,
            'price' => $price,
            'duration_days' => $duration_days,
            'is_active' => $is_active
        );

        // Push to "data"
        array_push($packages_arr['data'], $package_item);
    }

    // Turn to JSON & output
    echo json_encode($packages_arr);

} else {
    // No Packages
    http_response_code(404);
    echo json_encode(
        array('message' => 'No Packages Found')
    );
}
?>
