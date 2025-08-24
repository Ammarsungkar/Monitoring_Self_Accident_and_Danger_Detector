<?php
// Start session
session_start();

// Check if user is logged in
if(isset($_SESSION['user_id']) && $_SESSION['user_id']) {
  // Redirect to setting.php if logged in
  echo json_encode(array("redirect" => "setting.php"));
} else {
  // Redirect to login.php if not logged in
  echo json_encode(array("redirect" => "login.php"));
}
?>