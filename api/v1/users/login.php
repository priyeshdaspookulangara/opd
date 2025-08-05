<?php
// Start session
session_start();

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';
include_once '../models/User.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate user object
$user = new User($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->username) &&
    !empty($data->password)
) {
    $user->username = $data->username;
    $user->password = $data->password;

    // Attempt to login
    if($user->login()) {
        // Set session variables
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;

        http_response_code(200); // OK
        echo json_encode(
            array(
                'message' => 'Login successful.',
                'user_id' => $user->user_id,
                'username' => $user->username,
                'role' => $user->role
            )
        );
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(
            array('message' => 'Login failed. Invalid credentials.')
        );
    }
} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(
        array('message' => 'Login failed. Username and password are required.')
    );
}
?>
