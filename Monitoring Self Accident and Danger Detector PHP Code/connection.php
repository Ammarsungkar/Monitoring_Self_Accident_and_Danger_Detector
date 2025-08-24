<?php
$host= "localhost";
$user= "root";
$pass= "";
$database= "final";

$conn = mysqli_connect($host, $user, $pass, $database);
if ($conn){
    $open = mysqli_select_db($conn, $database);

}
else {
    die("Connection failed: " . mysqli_connect_error());
}

?>