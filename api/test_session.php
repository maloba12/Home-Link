<?php
require_once "../includes/auth.php";
header("Content-Type: application/json");
error_log("API Session ID: " . session_id());
error_log("API Session Data: " . json_encode($_SESSION));
echo json_encode([
    "session_id" => session_id(),
    "user_id" => $_SESSION["user_id"] ?? "not set",
    "is_logged_in" => isLoggedIn(),
    "username" => $_SESSION["username"] ?? "not set",
    "session_data_keys" => array_keys($_SESSION)
]);
?>
