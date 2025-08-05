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

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Prepare query
$query = "
    SELECT
        gender,
        COUNT(patient_id) as patient_count,
        AVG(DATEDIFF(CURDATE(), date_of_birth) / 365.25) as average_age
    FROM
        patients
    GROUP BY
        gender
";

$stmt = $db->prepare($query);
$stmt->execute();
$num = $stmt->rowCount();

if($num > 0) {
    $report_data = array();
    $report_data['data'] = array();
    $total_patients = 0;

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $item = array(
            'gender' => $gender,
            'patient_count' => (int)$patient_count,
            'average_age' => round($average_age, 1)
        );
        array_push($report_data['data'], $item);
        $total_patients += (int)$patient_count;
    }

    // Second query for overall stats
    $overall_query = "SELECT COUNT(patient_id) as total_patients, AVG(DATEDIFF(CURDATE(), date_of_birth) / 365.25) as overall_average_age FROM patients";
    $overall_stmt = $db->prepare($overall_query);
    $overall_stmt->execute();
    $overall_stats = $overall_stmt->fetch(PDO::FETCH_ASSOC);


    $report_data['summary'] = array(
        'total_patients' => (int)$overall_stats['total_patients'],
        'overall_average_age' => round($overall_stats['overall_average_age'], 1)
    );

    echo json_encode($report_data);
} else {
    echo json_encode(
        array(
            'message' => 'No patient data found.',
            'data' => []
        )
    );
}
?>
