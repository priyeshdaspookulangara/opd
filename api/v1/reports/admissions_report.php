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
        a.admission_id,
        a.ipd_number,
        p.first_name,
        p.last_name,
        w.ward_name,
        b.bed_number,
        a.admission_date,
        a.discharge_date,
        a.notes AS admission_notes
    FROM
        admissions a
    JOIN
        patients p ON a.patient_id = p.patient_id
    JOIN
        beds b ON a.bed_id = b.bed_id
    JOIN
        wards w ON b.ward_id = w.ward_id
    WHERE
        a.admission_date BETWEEN :start_date AND :end_date
    ORDER BY
        a.admission_date DESC
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
    $discharged_count = 0;

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $item = array(
            'admission_id' => $admission_id,
            'ipd_number' => $ipd_number,
            'patient_name' => $first_name . ' ' . $last_name,
            'ward_name' => $ward_name,
            'bed_number' => $bed_number,
            'admission_date' => $admission_date,
            'discharge_date' => $discharge_date,
            'admission_notes' => $admission_notes
        );
        array_push($report_data['data'], $item);
        if(!is_null($discharge_date)) {
            $discharged_count++;
        }
    }

    $report_data['summary'] = array(
        'total_admissions' => $num,
        'total_discharged' => $discharged_count,
        'start_date' => $start_date,
        'end_date' => $end_date
    );

    echo json_encode($report_data);
} else {
    echo json_encode(
        array(
            'message' => 'No admission records found for the selected date range.',
            'data' => []
        )
    );
}
?>
