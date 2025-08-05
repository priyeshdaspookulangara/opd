<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/User.php';

// Enforce admin-only access
include_once '../auth/validate_session.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403); // Forbidden
    echo json_encode(array('message' => 'Access denied. Only admins can create users.'));
    exit();
}


// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate user object
$user = new User($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->username) &&
    !empty($data->password) &&
    !empty($data->role) &&
    !empty($data->first_name) &&
    !empty($data->last_name)
) {
    $user->username = $data->username;
    $user->password = $data->password;
    $user->role = $data->role;
    $user->first_name = $data->first_name;
    $user->last_name = $data->last_name;

    // Create user
    if($user->create()) {
        http_response_code(201); // Created
        echo json_encode(
            array('message' => 'User Created')
        );
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(
            array('message' => 'User Not Created')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'User Not Created. Incomplete data.')
    );
}
?>
