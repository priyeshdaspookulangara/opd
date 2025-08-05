<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../auth/validate_session.php';
// This could be restricted to 'Admin', 'Doctor', 'Pharmacist'
// For now, any logged in user can see the list.

include_once '../config/database.php';
include_once '../models/Drug.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate drug object
$drug = new Drug($db);

// Drug query
$result = $drug->read();
$num = $result->rowCount();

if($num > 0) {
    $drugs_arr = array();
    $drugs_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $drug_item = array(
            'drug_id' => $drug_id,
            'drug_name' => $drug_name,
            'description' => $description,
            'cost_per_unit' => $cost_per_unit,
            'stock_quantity' => $stock_quantity
        );
        array_push($drugs_arr['data'], $drug_item);
    }
    echo json_encode($drugs_arr);
} else {
    // No Drugs
    echo json_encode(
        array('data' => array())
    );
}
?>
