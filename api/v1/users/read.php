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
include_once '../models/User.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate user object
$user = new User($db);

// User query
$result = $user->read();
$num = $result->rowCount();

if($num > 0) {
    $users_arr = array();
    $users_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $user_item = array(
            'user_id' => $user_id,
            'username' => $username,
            'role' => $role,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'is_active' => $is_active
        );
        array_push($users_arr['data'], $user_item);
    }
    echo json_encode($users_arr);
} else {
    echo json_encode(
        array('message' => 'No Users Found')
    );
}
?>
