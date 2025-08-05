<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then return an error
if(!isset($_SESSION["user_id"])){
    http_response_code(401); // Unauthorized
    // We echo a JSON message because the frontend expects JSON
    echo json_encode(array("message" => "Access Denied. Please login."));
    exit; // Stop further script execution
}

/**
 * Optional: Role-specific validation function.
 * This can be called from other scripts after including this file.
 * e.g., require_role('Admin');
 */
function require_role($role_to_check) {
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== $role_to_check) {
        http_response_code(403); // Forbidden
        echo json_encode(array("message" => "Access Denied. You do not have sufficient privileges."));
        exit;
    }
}
?>
