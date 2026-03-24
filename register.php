<?php
include "connect.php";

$name = $_GET['name'];
$email = $_GET['email'];
$password = $_GET['password'];
$role = $_GET['role'];

$sql = "INSERT INTO users (name, email, password, role) 
        VALUES ('$name', '$email', '$password', '$role')";

if (mysqli_query($conn, $sql)) {
    echo "User Registered Successfully";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
