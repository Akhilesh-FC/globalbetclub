<?php

$servername = "localhost";
$username = "u483840386_globalbetclub";
$password = "u483840386_Globalbetclub";
$dbname = "u483840386_globalbetclub";

// Create connection  
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

