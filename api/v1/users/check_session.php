<?php
include_once '../../auth/validate_session.php';

// If the script reaches here, the session is valid.
http_response_code(200);
echo json_encode(array(
    "status" => "success",
    "message" => "Session is valid.",
    "user_id" => $_SESSION['user_id'],
    "username" => $_SESSION['username'],
    "role" => $_SESSION['role']
));
?>
