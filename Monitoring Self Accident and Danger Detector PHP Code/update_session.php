<?php
session_start();

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Decode the JSON data received from the client
    $data = json_decode(file_get_contents("php://input"));

    // Update session data
    $_SESSION['settings'] = $data->settings;
    $_SESSION['numberCount'] = $data->numberCount;
    $_SESSION['messageAdded'] = $data->messageAdded;

    // Respond with success message
    echo json_encode(array("success" => true, "message" => "Session data updated successfully."));
} else {
    // Respond with error message if the request method is not POST
    echo json_encode(array("success" => false, "message" => "Invalid request method."));
}
?>

