<?php
$servername = "localhost";
$username = "u483840386_globalbetclub";
$password = "u483840386_Globalbetclub";
$dbname = "u483840386_globalbetclub";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo data($conn,1);
function data($conn,$id){
$query=mysqli_fetch_assoc(mysqli_query($conn,"SELECT `longtext` FROM `admin_settings` WHERE id=$id LIMIT 1;"));
return $query['longtext'];
}


 //$conn->close();
?>