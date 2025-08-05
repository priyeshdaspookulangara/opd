<?php
// Initialize the session
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Return a success response
http_response_code(200);
echo json_encode(array("message" => "Logout successful."));
?>
