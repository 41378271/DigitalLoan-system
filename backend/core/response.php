<?php
/**
 * Core API Response Helpers
 * Standardizes all JSON responses across the platform.
 */

function jsonSuccess($data = [], $message = "") {
    header("Content-Type: application/json");
    
    $response = ["success" => true];
    
    if (!empty($message)) {
        $response["message"] = $message;
    }
    
    // Merge additional data points
    if (!empty($data) && is_array($data)) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response);
    exit;
}

function jsonError($message, $code = 400, $extra_data = []) {
    header("Content-Type: application/json");
    http_response_code($code);
    
    $response = [
        "success" => false,
        "message" => $message
    ];
    
    if (!empty($extra_data) && is_array($extra_data)) {
        $response = array_merge($response, $extra_data);
    }
    
    echo json_encode($response);
    exit;
}
?>
