<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Enforce admin-only access
include_once '../auth/validate_session.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403); // Forbidden
    echo json_encode(array('message' => 'Access denied.'));
    exit();
}

include_once '../config/database.php';

// Get date range from query parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

if(!$start_date || !$end_date) {
    http_response_code(400);
    echo json_encode(array('message' => 'Please provide both a start and end date.'));
    exit();
}


// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Prepare query
$query = "
    SELECT
        b.bill_id,
        p.first_name,
        p.last_name,
        b.description,
        b.amount,
        b.bill_date
    FROM
        billings b
    JOIN
        patients p ON b.patient_id = p.patient_id
    WHERE
        b.bill_date BETWEEN :start_date AND :end_date
    ORDER BY
        b.bill_date DESC
";

$stmt = $db->prepare($query);

// Bind parameters
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);

// Execute query
$stmt->execute();
$num = $stmt->rowCount();

if($num > 0) {
    $report_data = array();
    $report_data['data'] = array();
    $total_revenue = 0;

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $item = array(
            'bill_id' => $bill_id,
            'patient_name' => $first_name . ' ' . $last_name,
            'description' => $description,
            'amount' => $amount,
            'bill_date' => $bill_date,
        );
        array_push($report_data['data'], $item);
        $total_revenue += floatval($amount);
    }

    $report_data['summary'] = array(
        'total_records' => $num,
        'total_revenue' => $total_revenue,
        'start_date' => $start_date,
        'end_date' => $end_date
    );

    echo json_encode($report_data);
} else {
    echo json_encode(
        array(
            'message' => 'No billing records found for the selected date range.',
            'data' => []
        )
    );
}
?>
